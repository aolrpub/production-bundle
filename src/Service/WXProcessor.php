<?php

namespace Aolr\ProductionBundle\Service;

use Aolr\ProductionBundle\Entity\Article;
use Aolr\ProductionBundle\Entity\Author;
use Aolr\ProductionBundle\Entity\BackItem;
use Aolr\ProductionBundle\Entity\Content;
use Aolr\ProductionBundle\Entity\DisplayObject;
use Aolr\ProductionBundle\Entity\Formula;
use Aolr\ProductionBundle\Entity\Permission;
use Aolr\ProductionBundle\Entity\Reference;
use Aolr\ProductionBundle\Entity\Section;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

class WXProcessor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var SluggerInterface
     */
    private $slugger;

    public function __construct(LoggerInterface $logger, Formatter $formatter, SluggerInterface $slugger)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
        $this->slugger = $slugger;
    }

    public function processSectionHead(Section $section, string $htmlString)
    {
        $htmlString = $this->processText($htmlString);
        $rawText = trim(strip_tags($htmlString));

        $getComputedId = function($text) {
            if (preg_match('/^([\d\.]+)\./', $text, $matches)) {
                return 'sec' . str_replace('.', 'dot', $matches[1]);
            }
            return 'sec' . $this->slugger->slug($text);
        };
        $getComputedType = function (Section $section) {
            $typeArr = [
                'intro' => 'introduction',
                'materials' => 'materials',
                'methods' => 'methods',
                'results' => 'results',
                'discussion' => 'discussion'
            ];

            $textArr = explode(' and ', strtolower($section->getTitleText()));
            $types = array_map(function ($text) use ($typeArr) {
                $key = array_search($text, $typeArr);
                return $key === false ? '' : $key;
            }, $textArr);
            $res = trim(implode('|', $types));

            return !empty($res) ? $res : '';
        };

        $section->setTitle($htmlString)->setType($getComputedType($section))->setId($getComputedId($rawText));
    }

    public function processFigure(DisplayObject $displayObject, string $figure)
    {
        $displayObject->setType(DisplayObject::TYPE_FIG)->setInfo($this->formatter->formatString($figure));
        if (preg_match('/data-name="([^"]+)"/', $figure, $matches)) {
            $displayObject->setGraphicHref($matches[1]);
        }
    }

    public function processFormula(Crawler $crawler)
    {
        $formulas = $crawler->filter('tr')->each(function (Crawler $tr) {
            $formula = new Formula();
            $formula->setType(Content::TYPE_FORMULA);
            $formulaEle = $tr->filter('td')->eq(0);
            if (trim($formulaEle->text()) == '') {
                return null;
            }
            if ($formulaEle->count() > 0) {
                $info = $this->formatFormulaString($formulaEle->html());
                $formula->setInfo($info);
            }
            $labelEle = $tr->filter('td')->eq(1);
            if ($labelEle->count() > 0) {
                $text = preg_replace('/\s+/', '', $labelEle->text());
                $formula->setLabel($text);
                if (preg_match('/\d+/', $text, $matches)) {
                    $formula->setId($matches[0]);
                }
            }
            return $formula;
        });

        return array_filter($formulas);
    }

    public function processCopyright(Permission $permission, string $string)
    {
        $string = trim(preg_replace('/copyright:*/i', '', $string));

        $permission->setYear(date('Y'));
        if (preg_match('/\d{4}/', $string, $matches)) {
            $year = ($matches[0] > 1900 && $matches[0] <= $permission['year']) ? $matches[0] : $permission['year'];
            $permission->setYear($year);
        }

        if (preg_match('/(©.*?\.)(.*)$/', $string, $matches)) {
            $permission->setStatement($matches[1]);
            $license = preg_replace_callback('/<uri>(.*?)<\/uri>/', function ($matches) {
                return '<ext-link ext-link-type="uri" xlink:href="' . $matches[1] . '">' . $matches[1] . '</ext-link>';
            }, $this->processText(trim($matches[2])));
            $permission->setLicenseContents([$license]);
        }

        return $permission;
    }

    private function formatFormulaString(string $string)
    {
        $string = preg_replace('/<\/?(p|span)[^>]*>/', '', $string);
        $string = html_entity_decode($string);

        $string = preg_replace(['/(<annotation.*?<\/annotation>)/', '/>\s+</'], ['', '><'], $string);
        $string = preg_replace('/(<\/?)/', '$1mml:', $string);
        $crawler = new Crawler();
        $crawler->addXmlContent($string);
        return $crawler->outerHtml();
    }

    public function processTable(DisplayObject $displayObject, Crawler $crawler)
    {
        $trs = $crawler->filter('tr')->each(function(Crawler $ele, $k) {
            $isHeader = false;
            if ($k == 0) {
                $isHeader = true;
                $tdCrawler = $ele->filter('td');
                foreach ($tdCrawler as $k => $v) {
                    $tdEle = $tdCrawler->eq($k);
                    if ($tdEle->filter('b')->count() == 0) {
                        $isHeader = false;
                    }
                }
            }

            $tds = $ele->filter('td')->each(function(Crawler $ele) use ($isHeader) {
                $extractTdAttrs = $ele->extract(['colspan', 'align', 'valign', 'style'])[0];
                $tdAttrs = array_combine(['colspan', 'align', 'valign', 'style'], $extractTdAttrs);
                $tdAttrs['style'] = preg_replace(
                    ['/windowtext 1(.0)?pt/', '/border-(top|right|bottom|left):none;/', '/width:.*?;/'],
                    ['thin', '', ''],
                    $tdAttrs['style']
                );
                $p = $ele->filter('p');
                if ($p->count() > 0) {
                    $extractPAttrs = $p->extract(['align', 'style'])[0];
                    $tdAttrs['align'] = $extractPAttrs[0];

                    if (empty($tdAttrs['align']) && preg_match('/text-align:(.*?);/', $extractPAttrs[1], $matches)) {
                        $tdAttrs['align'] = $matches[1];
                    }
                }

                $content = preg_replace(['/<p\s+.*?>([\s\S]+?)<\/p>/', '/<span\s+.*?>([\s\S]+?)<\/span>/'], ['$1', '$1'], $ele->html());
                $content = preg_replace('/<img([^>]+)\/?>/', '<img$1/>', $content);
                if ($isHeader) {
                    $content = preg_replace('/<b\s+.*?>([\s\S]+?)<\/b>/', '$1', $content);
                }

                $attrStrings = ' ';
                foreach ($tdAttrs as $keyName => $tdAttr) {
                    if ($tdAttr != '') {
                        $attrStrings .= $keyName . '="' . $tdAttr . '" ';
                    }
                }
                $content = $this->processText($content);

                if ($isHeader) {
                    return '<th' . $attrStrings . '>' . $content . '</th>';
                }

                return '<td' . $attrStrings . '>' . $content .  '</td>';
            });

            return [
                'isHeader' => $isHeader,
                'html' => '<tr>' . implode('', $tds) . '</tr>'
            ];
        });

        $htmlString = '<table>';
        $headers = array_filter($trs, function($tr) {
            return $tr['isHeader'];
        });

        if (!empty($headers)) {
            $htmlString .= '<thead>' . implode('', array_map(function($header) { return  $header['html']; }, $headers)) . '</thead>';
        }

        $bodys = array_filter($trs, function($tr) {
            return !$tr['isHeader'];
        });

        if (!empty($bodys)) {
            $htmlString .= '<tbody>' . implode('', array_map(function($body) { return  $body['html']; }, $bodys)) . '</tbody>';
        }
        $htmlString .= '</table>';
        $displayObject->setType(DisplayObject::TYPE_TABLE);
        $displayObject->setInfo($htmlString);
    }

    public function processObjectCaption(DisplayObject $displayObject, string $htmlString)
    {
        $rawText = trim(strip_tags($htmlString));
        if (preg_match('/((Table|Figure)\s+\S+?\.?)\s+/i', $rawText, $matches)) {
            $displayObject
                ->setLabel(trim($matches[1], '.'))
                ->setType(strtolower($matches[2]) == 'table' ? DisplayObject::TYPE_TABLE : DisplayObject::TYPE_FIG);
        }

        $contentInfo = preg_replace(['/^<b>\s*((Table|Figure)\s+\S+?\.?)\s*<\/b>/i', '/(^(Table|Figure)\s+\S+?\.?)\s*/i'], ['', ''], trim($htmlString));
        $displayObject->setCaption($this->processText($contentInfo));

        $displayObject->setId($this->getComputedId($displayObject->getLabel()));
        return $displayObject;
    }

    public function getComputedId(string $label): string
    {
        $id = '000';
        if (preg_match('/(Table|Figure)\s+(.*)$/i', $label, $matches)) {
            $type = strtolower($matches[1]) == 'table' ? DisplayObject::TYPE_TABLE : DisplayObject::TYPE_FIG;
            $id = $type . substr('000' . $matches[2], -3);
        }

        return $id;
    }

    public function processSections(Article $article)
    {
        $sections = $article->getSections();
        foreach ($sections as $section) {
            $this->processSection($section);
        }
    }

    private function processSection(Section $section)
    {
        $contents = $section->getContents();
        /** @var Content $content */
        foreach ($contents as $content) {
            $content->setInfo($this->processSecText($content->getInfo(), $section->getArticle()));
        }
        $sections = $section->getChildren();
        if ($sections->count() == 0) {
            return '';
        }
        foreach ($sections as $childSection) {
            $this->processSection($childSection);
        }
    }

    public function processReferences(ArrayCollection $references, Article $article)
    {
        $rawTexts = $references->map(function (Reference $reference) {
            return $reference->getRawText() ?: '';
        });

        try {
            $client = new Client();
            $res = $client->request('POST', 'http://localhost:4567', [
                'form_params' => [
                    'references' => implode("\n", $rawTexts->getValues())
                ]
            ]);

            $res = json_decode($res->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $res = [];
            $this->logger->critical($e->getMessage());
        }

        if (!empty($res) && $references->count() != count($res)) {
            $this->logger->critical('Anystyle is not ok, count is not same as original references count');
        }

        /** @var Reference $currentRef */
        foreach ($references as $k => $currentRef) {
            $item = $res[$k] ?? [];
            $publicationType = $this->getPublicationType($this->getPublicationType($item['type'] ?? null));
            $item = $this->preFormatSpecialCase($item, $publicationType);

            $id = $k + 1;
            $currentRef->setId($id . '-' . $article->getId())->setLabel($id . '.');
            foreach ($item['author'] ?? [] as $author) {
                if (empty($author['family']) || empty($author['given'])) {
                    continue;
                }
                $authorObj = new Author();
                $authorObj->setSurname($author['family'])->setGivenName($author['given']);
                $currentRef->addPerson($authorObj);
            }


            $currentRef->setArticleTitle($item['title'][0] ?? '');
            $currentRef->setYear((int)($item['date'][0] ?? null));
            $currentRef->setVolume($item['volume'][0] ?? null);

            if (!empty($item['pages'][0]) && preg_match('/([^,–]+)?(–([^,]+))?,?/', $item['pages'][0] ?? '', $matches)) {
                $currentRef->setFPage($matches[1]);
                $currentRef->setLPage($matches[3] ?? null);
            }

            $currentRef->setPublicationType($publicationType);
            $currentRef->setSource($item['container-title'][0] ?? '');
            $currentRef->setDoi($item['doi'][0] ?? null);
        }

        return $references;
    }

    private function preFormatSpecialCase(array $item, $publicationType)
    {
        if ($publicationType == 'journal' && empty($item['container-title']) && !empty($item['note'])) {
            if (preg_match('/(.*?)\s(\d{4}),\s(\d+),\s(.*?,)/', $item['note'][0], $matches)) {
                $item['container-title'][0] = $matches[1];
                $item['date'][0] = $matches[2];
                $item['volume'][0] = $matches[3];
                $item['pages'][0] = $matches[4];
            }
        }

        return $item;
    }

    private function getPublicationType(?string $type): ?string
    {
        switch ($type) {
            case 'article-journal':
                $type = 'journal';
                break;
            default:
                break;
        }

        return $type;
    }

    public function processAbstract(?string $string)
    {
        if (empty($string)) {
            return null;
        }

        $abstract = preg_replace(['/^<b>\s*Abstract:?\s*<\/b>/i', '/^Abstract:?\s*/'], ['', ''], trim($string));
        return $this->processText($abstract);
    }

    public function processKeywords(?string $string)
    {
        if (empty($string)) {
            return null;
        }

        $string = preg_replace(['/^<b>\s*Keywords:?\s*<\/b>/i', '/^Keywords:?\s*/'], ['', ''], trim($string));
        return $this->processText($string);
    }

    public function processSecText(?string $text, Article $article)
    {
        if (empty($text)) {
            return null;
        }

        $text = $this->processText($text);

        /**
         * process reference
         */
        $text = preg_replace_callback('/\[([\d,–\s]+)\]/', function($matches) use ($article) {
            $refs = $matches[1];
            $refs = preg_replace_callback('/(\d+)–(\d+)/', function ($matchesRange) {
                return implode(',', range($matchesRange[1], $matchesRange[2]));
            }, $refs);
            $refsArr = preg_split('/\s*,\s*/', $refs);
            $refsArr = array_map(function ($ref) use ($article) {
                return '<xref ref-type="bibr" rid="B' . $ref . ($article->getId() ? '-' . $article->getId() : '') .'">' . $ref . '</xref>';
            }, $refsArr);

            return '[' . implode(',', $refsArr) . ']';
        }, $text);

        /**
         * display object
         */
        $text = preg_replace_callback('/(Table|Figure)\s+([^\s.,]+)/', function ($matches) use ($article) {
            $displayObject = $article->getDisplayObjects()->filter(function (DisplayObject $object) use ($matches) {
                return $object->getLabel() == $matches[0];
            })->first();

            if (!empty($displayObject)) {
                return $this->renderXref($displayObject, $article);
            }

            /** @var BackItem $app */
            foreach($article->getAppBackItems() as $app) {
                $contents = $app->getContents();
                foreach ($contents as $content) {
                    if ($content instanceof DisplayObject) {
                        if ($content->getLabel() == $matches[0]) {
                            return $this->renderXref($content, $article, $matches[0]);
                        }
                    } else if ($content instanceof Content
                        && (stripos($app->getTitle(), 'supplement') || stripos($app->getTitle(), 'material'))) {
                        if (strpos($content->getInfo(), $matches[0]) !== false) {
                            return $this->renderXref($app, $article, $matches[0]);
                        }
                    }
                }
            }
            return $matches[0];
        }, $text);

        return $text;
    }

    private function renderXref($object, Article $article, string $rawText = '')
    {
        if ($object instanceof DisplayObject) {
            $id = $object->getId() . ($article->getId() ? '-' . $article->getId() : '');
            return '<xref ref-type="' . $object->getType() . '" rid="' . $id . '">' . $object->getlabel() . '</xref>';
        }

        if ($object instanceof BackItem) {
            $id = 'app' . $object->getOrderNumber() . ($article->getId() ? '-' . $article->getId() : '');
            return '<xref ref-type="app" rid="' . $id . '">' . $rawText . '</xref>';
        }

        return '';
    }

    public function processText(?string $text)
    {
        if (empty($text)) {
            return null;
        }
        $text = $this->removeTags($text);
        $text = $this->formatter->formatString($text);

        return $this->convertTags($text);
    }

    public function removeTags(?string $text)
    {
        return preg_replace('/(<span[\s\S]*?>|<\/span>|<p[\s\S]*?>|<\/p>)/', '', $text);
    }

    public function convertTags(string $text)
    {
        $text = preg_replace(
            ['/<i>([\s\S]*?)<\/i>/', '/<b>([\s\S]*?)<\/b>/', '/<a[^>]*>([\s\S]*?)<\/a>/'],
            ['<italic>$1</italic>', '<bold>$1</bold>', '$1'],
            $text
        );

        return preg_replace_callback('/(<uri>)?(http.+?)[\s\),]/', function ($matches) {
            if (!empty($matches[1])) {
                return $matches[0];
            }
            return str_replace($matches[2], '<uri>' . $matches[2] . '</uri>', $matches[0]);
        }, $text);
    }
}
