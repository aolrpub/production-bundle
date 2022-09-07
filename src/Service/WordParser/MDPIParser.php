<?php

namespace Aolr\ProductionBundle\Service\WordParser;

use Aolr\ProductionBundle\Entity\Affiliation;
use Aolr\ProductionBundle\Entity\App;
use Aolr\ProductionBundle\Entity\Article;
use Aolr\ProductionBundle\Entity\Author;
use Aolr\ProductionBundle\Entity\BackItem;
use Aolr\ProductionBundle\Entity\Content;
use Aolr\ProductionBundle\Entity\DisplayObject;
use Aolr\ProductionBundle\Entity\Journal;
use Aolr\ProductionBundle\Entity\License;
use Aolr\ProductionBundle\Entity\Note;
use Aolr\ProductionBundle\Entity\Permission;
use Aolr\ProductionBundle\Entity\Reference;
use Aolr\ProductionBundle\Entity\Section;
use Aolr\ProductionBundle\Service\ApiManager;
use Aolr\ProductionBundle\Service\Formatter;
use Aolr\ProductionBundle\Service\WXProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DomCrawler\Crawler;

class MDPIParser extends AbstractParser
{
    use ParserTrait;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var WXProcessor
     */
    private $wxProcessor;

    /**
     * @var ApiManager
     */
    private $apiManager;

    /**
     * @var Section|App
     */
    private $currentContainer;

    /**
     * @var DisplayObject|null
     */
    private $currentDisplayObject;

    private $indexes = [
        'current' => 0,
        'affiliation' => null,
        'section' => null,
        'back_matter' => null,
        'references' => null,
    ];

    private $elements = [
        'prev' => null,
        'current' => null,
        'next' => null
    ];

    public function __construct(Formatter $formatter, WXProcessor $WXProcessor, ApiManager $apiManager)
    {
        $this->formatter = $formatter;
        $this->wxProcessor = $WXProcessor;
        $this->apiManager = $apiManager;
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function getData(): Article
    {
        $this->parseHeader();
        $this->parseBody();
        $this->parseFooter();
        $this->afterProcess();
        return $this->article;
    }

    private function parseHeader(): void
    {
        $filter = $this->crawler->filter('header');
        if ($filter->count() === 0) {
            return;
        }

        $text = $filter->text();

        if (preg_match('/(.*?)(\s+\d{4}),\s*(\d+)\s*/', $text, $matches)) {
            $journal = $this->article->getJournal() ?: new Journal();

            if (empty($journal->getTitle())) {
                $journal->setTitle($matches[1]);
                $this->article->setJournal($journal);
            }
            if (empty($this->article->getVolume())) {
                $this->article->setVolume($matches[3]);
            }
        }
    }

    private function parseFooter(): void
    {
        $filter = $this->crawler->filter('footer');
        if ($filter->count() === 0) {
            return;
        }

        $text = $filter->text();

        if (preg_match('/(.*?)(\s+\d{4}),\s*(\d+).*?(10.\d+\/.*?)\s+/', $text, $matches)) {
            $journal = $this->article->getJournal() ?: new Journal();

            if (empty($journal->getTitle())) {
                $journal->setTitle($matches[1]);
                $this->article->setJournal($journal);
            }
            if (empty($this->article->getVolume())) {
                $this->article->setVolume($matches[3]);
            }

            if (empty($this->article->getDoi())) {
                $this->article->setDoi($matches[4]);
            }
        }
    }

    private function parseBody()
    {
        $filter = $this->crawler->filter('article');
        if ($filter->count() === 0) {
            $filter = $this->crawler->filter('body');
        }

        if ($filter->count() === 0) {
            throw new \Exception('wrong exported html file');
        }

        $children = $filter->children();

        /**
         * @var int $k
         * @var \DOMDocument $child
         */
        foreach ($children as $k => $child) {

            $this->elements['current'] = $children->eq($k);
            if (trim($this->elements['current']->text()) == '' && !$this->hasResource($this->elements['current'])) {
                continue;
            }
            $this->elements['prev'] = $children->eq($k - 1);
            $this->elements['next'] = $children->eq($k + 1);

            $this->indexes['current'] = $k;
            $this->processElement($this->elements['current']);
//            if ($k == 33) {
//                dump($this->elements['current']->outerHtml());
//                dump($this->currentDisplayObject);
//
//                exit;
//            }
        }
    }

    private function afterProcess()
    {
//        $this->setArticleId();
        $this->setPermission();
        $references = $this->wxProcessor->processReferences($this->article->getReferences(), $this->article);
        $this->article->setReferences($references);
        $this->wxProcessor->processSections($this->article);
    }

    private function processElement(Crawler $crawler)
    {
        $tag = strtolower($crawler->nodeName());
        if ($tag != 'table' && $crawler->filter('table')->count() == 1) {
            $crawler = $crawler->filter('table');
        }

        $classes = explode(' ', $crawler->attr('class'));
        $classes[0] = $classes[0] ?? '';
        if (in_array($tag, ['p', 'div'])) {
            if ($this->isReferenceTitle($crawler)) {
                $this->currentContainer = 'references';
                if (empty($this->indexes['references'])) {
                    $this->indexes['references'] = $this->indexes['current'];
                }
            } else if ($this->isType($classes)) {
                $this->data['type'] = $crawler->text();
                $this->article->setType($this->formatter->formatType($crawler->text()));
            } else if ($this->isTitle($classes)) {
                $this->article->setTitle($this->wxProcessor->processText($crawler->html()));
            } else if ($this->isAuthor($classes)) {
                $this->processAuthors($crawler);
            } else if ($this->isAffiliation($classes)) {
                $this->processAffiliation($crawler);
            } else if ($this->isAbstract($crawler)) { // stripos($classes[0], 'abstract') !== false &&
                $this->article->setAbstract($this->wxProcessor->processAbstract($crawler->html()));
            } else if ($this->isKeywords($crawler)) { // stripos($classes[0], 'keyword') !== false
                $text = $this->wxProcessor->processKeywords($crawler->html());
                $this->article->setKeywords(preg_split('/;\s*/', $text));
            } else if ($this->isFigure($crawler)) {
                $displayObject = new DisplayObject();
                $this->wxProcessor->processFigure($displayObject, $crawler->html());
                $this->addDisplayObject($displayObject);
            } else if ($this->isCaption($classes)) {
                $this->processCaption($crawler);
            } else if ($this->isTableFooter($crawler, $this->elements['prev'])) {
                $displayObject = new DisplayObject();
                $displayObject->setFooter($this->wxProcessor->processText($crawler->html()));
                $this->addDisplayObject($displayObject);
            } else if ($this->isSectionHead($crawler)) {
                if (empty($this->elements['section'])) {
                    $this->indexes['section'] = $this->elements['current'];
                }
                $this->processSectionHead($crawler, $classes[0]);
            } else if ($this->isSectionText($crawler)) {
                $this->processSectionText($crawler);
            }  else if ($this->isBackMatter($crawler)) {
                if (empty($this->indexes['back_matter'])) {
                    $this->indexes['back_matter'] = $this->indexes['current'];
                }
                $this->processBackMatter($crawler);
            } else if ($this->isReference()) {
                $text = $this->formatter->formatString($this->formatter->formatString($crawler->text()));
                $reference = new Reference();
                $reference->setRawText($text);
                $this->article->addReference($reference);
            } else {
                $this->guessElement($crawler);
            }
        } else {
            if ($this->isFormula($crawler)) {
                $formulas = $this->wxProcessor->processFormula($crawler);

                foreach ($formulas as $formula) {
                    $this->currentContainer->addContent($formula);
                    $this->article->addFormulas($formula);
                }

            } else if ($this->isTable($crawler)) {
                $this->processTable($crawler);
            }
        }

    }

    public function isType($classes): bool
    {
        return stripos($classes[0], 'articletype') !== false;
    }

    public function isTitle($classes): bool
    {
        return stripos($classes[0], 'title') !== false;
    }

    public function isAuthor($classes): bool
    {
        return stripos($classes[0], 'authorname') !== false;
    }

    public function isAffiliation($classes): bool
    {
        return stripos($classes[0], 'affiliation') !== false;
    }

    public function isCaption($classes): bool
    {
        return stripos($classes[0], 'caption') !== false;
    }

//    public function isReference($classes): bool
//    {
//        return preg_match('/(Reference|Bibliography)/i', $classes[0]);
//    }

//    public function isBackMatter($classes)
//    {
//        return stripos($classes[0], 'BackMatter');
//    }

    private function processBackMatter(Crawler $crawler)
    {
        $html = $crawler->html();
        $this->currentContainer = $backItem = new BackItem();
        if (preg_match('/^<b>(.*?):\s*<\/b>\s*(.*)$/', $html, $matches)) {

            $backItem
                ->setType(BackItem::TYPE_NOTES)
                ->setTitle($this->formatter->formatString(str_replace(['<b>', '</b>'], '', $matches[1])))
            ;

            if (!empty($matches[2])) {
                $content = new Content();
                $content->setType(Content::TYPE_TEXT)->setInfo($this->wxProcessor->processText($matches[2]));
                $backItem->addContent($content);
            } else {
                $this->currentContainer = $backItem;
            }

            if (preg_match('/Acknowledgments?$/i', $backItem->gettitle())) {
                $backItem->setType(BackItem::TYPE_ACK);
            } else if (preg_match('/Supplementary\sMaterials?$/i', $backItem->gettitle())) {
                $backItem->setType(BackItem::TYPE_APP);
                $backItem->setOrderNumber($this->article->getAppBackItems()->count() + 1);
            }
        } else {
            $content = new Content();
            $content->setType(Content::TYPE_TEXT)->setInfo($this->formatter->formatString($html));
            $backItem->addContent($content);
        }

        $this->article->addBackItem($backItem);
    }

    private function processCaption(Crawler $crawler)
    {
        $displayObject = new DisplayObject();
        $this->wxProcessor->processObjectCaption($displayObject, $crawler->html());
        $this->addDisplayObject($displayObject);
    }

    private function addDisplayObject(DisplayObject $displayObject)
    {
        if (!$this->currentDisplayObject instanceof DisplayObject) {
            $this->currentDisplayObject = $displayObject;
        } else {
            $attrs = ['id', 'label', 'caption', 'info', 'graphicHref', 'footer'];
            foreach ($attrs as $attr) {
                $getMethod = 'get' . $attr;
                $setMethod = 'set' . $attr;
                if (!empty($displayObject->$getMethod())) {
                    $this->currentDisplayObject->$setMethod($displayObject->$getMethod());
                }
            }

//            if ($displayObject->getType() == DisplayObject::TYPE_TABLE) {
//
//            }
            // current is footer OR next element is not table footer
            if (!empty($displayObject->getFooter()) || !$this->isTableFooter($this->elements['next'], $this->elements['current'])) {
                if ($this->currentContainer instanceof BackItem) {
                    $this->currentContainer->addContent($this->currentDisplayObject);
                } else {
                    $this->article->addDisplayObject($this->currentDisplayObject);
                }
                $this->currentDisplayObject = null;
            }

        }
    }

    private function processSectionText(Crawler $crawler)
    {
        $contentText = $this->wxProcessor->processText($crawler->html(), $this->article);

        $section = $this->currentContainer; //$this->getLastSection();
        $content = new Content();
        $content->setType(Content::TYPE_TEXT)->setInfo($contentText);
        $section->addContent($content);
    }

    private function processSectionHead(Crawler $crawler, $class)
    {
        $section = new Section();
        $section->setArticle($this->article);
        $this->wxProcessor->processSectionHead($section, $crawler->html());

        if ($class == 'MDPI21heading1') {
            $this->article->addSection($section);
        } else if ($class == 'MDPI22heading2') {
            /** @var Section $parentSection */
            $parentSection = $this->article->getSections()->last();
            $section->setParent($parentSection);
            $parentSection->addChild($section);
        } else if ($class == 'MDPI23heading3') {
            /** @var Section $lastSection */
            $lastSection = $this->article->getSections()->last();
            /** @var Section $parentSection */
            $parentSection = $lastSection->getChildren()->last();
            $section->setParent($parentSection);
            $parentSection->addChild($section);
        }

        $this->currentContainer = $section;
    }

    private function processTable(Crawler $crawler)
    {
        if ($crawler->filter('.MDPI14history')->count() > 0) {
            $this->processCopyrightAndHistory($crawler);
        } else {
            $displayObject = new DisplayObject();
            $displayObject->setArticle($this->article);
            $this->wxProcessor->processTable($displayObject, $crawler);
            $this->addDisplayObject($displayObject);
        }
    }

    private function guessElement(Crawler $crawler)
    {
        $text = $this->formatter->formatString($crawler->text());

        if (preg_match('/^Appendix/i', $text)) {
            $backItem = new BackItem();
            $backItem->setType(BackItem::TYPE_APP)->setTitle($text);
            $this->currentContainer = $backItem;
            $this->article->addBackItem($backItem);

        }
    }

    private function processAuthors(Crawler $crawler)
    {
        $text = $crawler->text();
        $authors = preg_split('/(,\s|\sand\s)/', $text);
        foreach ($authors as $author) {
            if (preg_match('/([\w\-\.]+)\s(.*?)\s*(\S*)$/', $author, $matches)) {
                $author = new Author();
                $author
                    ->setSurname($matches[1])
                    ->setGivenName($matches[2])
                    ->setAffs($matches[3])
                    ->setArticle($this->article)
                ;
                $this->article->addAuthor($author);
            }
        }
    }
    private function processAffiliation(Crawler $crawler)
    {
        $text = $this->formatter->formatString($crawler->text());

        $affiliation = new Affiliation();
        if (preg_match('/(\S*)\s+(.*)\s*$/', $text, $matches)) {
            $affiliation->setLabel($matches[1])->setContent($matches[2]);
            if (is_numeric($matches[1])) {
                $affiliation->setRorId($this->apiManager->getRorId($text));
            }
        } else {
            $affiliation->setContent($text)->setRorId($this->apiManager->getRorId($text));
        }
        $this->article->addAffiliation($affiliation);
    }

    private function processCopyrightAndHistory(Crawler $crawler): void
    {
        $elements = $crawler->filter('p');

        foreach ($elements as $k => $element) {
            $currentEle = $elements->eq($k);
             if ($currentEle->attr('class') == 'MDPI14history') {
                if (preg_match('/(Received|Accepted|Published):\s*(.*)\s*/i', $currentEle->text(), $matches)) {
                    $date = \Datetime::createFromFormat('j F Y', $matches['2']);
                    if ($date instanceof \DateTime) {
                        $date->setTime(0, 0, 0);
                        switch (strtolower($matches['1'])) {
                            case 'received':
                                $this->article->setReceivedDate($date);
                                break;
                            case 'accepted':
                                $this->article->setAcceptedDate($date);
                                break;
                            case 'published':
                                $this->article->setPublishedDate($date);
                                break;
                            default:
                                break;

                        }
                    }
                }
            }
//            else if (stripos($currentEle->text(), 'Copyright') !== false) {
//                 $permission = new Permission();
//                 $this->article->setPermission($permission);
//                 $this->wxProcessor->processCopyright($permission, $currentEle->text());
//            }
            else if ($currentEle->attr('class') == 'MDPI61Citation') {
                $this->article->setDoi($this->formatter->formatDoi($currentEle->text()));
            } else if ($currentEle->attr('class') == 'MDPI63Notes' && stripos($currentEle->text(), 'Note:')) {
                $this->article->addFootNote($this->wxProcessor->processText($currentEle->html()));
            }
        }
    }

    private function setPermission()
    {
        $permission = new Permission();
        $permission
            ->setStatement('&#xA9; 2021 by the authors.')
            ->setYear(date('Y'))
            ->setLicenseType('open-access')
            ->setLicenseContents([
                'Licensee MDPI, Basel, Switzerland. This article is an open access article distributed under the terms and conditions of the Creative Commons Attribution (CC BY) license (<ext-link ext-link-type="uri" xlink:href="https://creativecommons.org/licenses/by/4.0/">https://creativecommons.org/licenses/by/4.0/</ext-link>).'
            ])
        ;
        $this->article->setPermission($permission);
    }
}
