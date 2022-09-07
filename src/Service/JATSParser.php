<?php

namespace Aolr\ProductionBundle\Service;

use Aolr\ProductionBundle\Entity\Affiliation;
use Aolr\ProductionBundle\Entity\Article;
use Aolr\ProductionBundle\Entity\Author;
use Aolr\ProductionBundle\Entity\BackItem;
use Aolr\ProductionBundle\Entity\Content;
use Aolr\ProductionBundle\Entity\DisplayObject;
use Aolr\ProductionBundle\Entity\Editor;
use Aolr\ProductionBundle\Entity\Journal;
use Aolr\ProductionBundle\Entity\License;
use Aolr\ProductionBundle\Entity\Permission;
use Aolr\ProductionBundle\Entity\Reference;
use Aolr\ProductionBundle\Entity\Section;
use Symfony\Component\DomCrawler\Crawler;

class JATSParser implements ParserInterface
{
    /**
     * @var Article
     */
    private $article;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
        $this->article = new Article();
    }

    public function parse(string $filePath): Article
    {
        $string = file_get_contents($filePath);
//        $string = $this->formatXml($string);

        $crawler = new Crawler();
        $crawler->addXmlContent($string);

        $this->processJournal($crawler->filter('article > front > journal-meta'));
        $this->article->setType($this->getAttr($crawler->filter('article'), 'article-type'));
        if (($articleMeta = $crawler->filter('article > front > article-meta')) && $articleMeta->count() > 0) {
            $this->processArticle($articleMeta);
            $this->processCategory($articleMeta);
            $this->processAuthors($articleMeta);
            $this->processEditors($articleMeta);
            $this->processAffiliation($articleMeta);
            $this->processDates($articleMeta);
            $this->processPermissions($articleMeta);
        }
        if (($bodyEle = $crawler->filter('article > body')) && $bodyEle->count() > 0) {
            $this->processSections($bodyEle);
        }

        if (($backEle = $crawler->filter('article > back')) && $backEle->count() > 0) {
            $this->processBack($backEle);
        }

        return $this->article;
    }

    private function formatXml(string $string)
    {
        return preg_replace('/<\?.*?\?>/', '', $string);
    }

    private function processJournal(Crawler $crawler)
    {
        $journal = new Journal();
        $this->article->setjournal($journal);

        if ($crawler->count() == 0) {
            $this->article->setJournal($journal);
            return;
        }
        $journal
            ->setTitle($this->getBySelector($crawler, 'journal-title-group > journal-title', 'text'))
            ->setAbbrevTitle($this->getBySelector($crawler, 'journal-title-group > abbrev-journal-title', 'text'))
            ->setPublisherName($this->getBySelector($crawler, 'publisher > publisher-name', 'text'))
            ->setEIssn($this->getBySelector($crawler, 'issn[pub-type="epub"]', 'text'))
            ->setIssn($this->getBySelector($crawler, 'issn[pub-type="ppub"]', 'text'));
    }

    private function processArticle(Crawler $crawler)
    {
        $this->article
            ->setId($this->getBySelector($crawler, 'article-id[pub-id-type="publisher-id"]', 'text'))
            ->setDoi($this->getBySelector($crawler, 'article-id[pub-id-type="doi"]', 'text'))
            ->setTitle($this->getBySelector($crawler, 'title-group > article-title'))
            ->setVolume($this->getBySelector($crawler, 'volume', 'text'))
            ->setIssue($this->getBySelector($crawler, 'issue', 'text'))
            ->setNumber($this->getBySelector($crawler, 'elocation-id', 'text'))
            ->setAbstract($this->formatHtmlString($this->getBySelector($crawler, 'abstract')))
            ->setKeywords($crawler->filter('kwd-group > kwd')->each(function (Crawler $kw) {
                return trim($kw->html());
            }));
    }

    private function processBack(Crawler $crawler)
    {
        $crawler->children()->each(function (Crawler $item) {
            $tag = $item->nodeName();
            if (in_array($tag, ['ack', 'notes', 'app-group', 'glossary'])) {
                $this->processBackNote($item);
            } else if ($tag == 'ref-list') {
                $this->processReferences($item);
            } else if ($tag == 'sec') {
                $secType = $this->getAttr($item, 'sec-type');
                if ($secType == 'display-objects') {
                    $this->processDisplayObjects($item);
                }
            } else if ($tag == 'fn-group') {
                $item->filter('fn')->each(function (Crawler $fn) {
                    $this->article->addFootnote($this->getBySelector($fn, 'p'));
                });
            }
        });
    }

    private function processDisplayObjects(Crawler $sec)
    {
        $sec->children()->each(function (Crawler $item) {
            $tag = $item->nodeName();
            if ($tag != 'title') {

                $type = $tag;
                foreach (DisplayObject::$types as $dType) {
                    if (stripos($tag, $dType) !== false) {
                        $type = $dType;
                    }
                }
                $displayObject = new DisplayObject();
                $this->article->addDisplayObject($displayObject);

                $displayObject->setArticle($this->article);
                $displayObject
                    ->setId($this->getAttr($item, 'id'))
                    ->setLabel($this->getBySelector($item, 'label', 'text'))
                    ->setCaption($this->getBySelector($item, 'caption > p', 'text'))
                    ->setGraphicHref($this->getAttr($item->filter('graphic'), 'xlink:href'))
                    ->setType($type)
                    ->setInfo($this->getBySelector($item, 'table,graphic', 'outerHtml'))
                    ->setFooter($this->getBySelector($item, 'table-wrap-foot p'))
                ;
            }
        });
    }

    private function processReferences(Crawler $refs)
    {
        $refs->children('ref')->each(function (Crawler $refEle) {
            $ref = new Reference();
            $this->article->addReference($ref);
            $ref
                ->setId($this->formatId($this->getAttr($refEle, 'id')))
                ->setLabel($this->getBySelector($refEle, 'label', 'text'))
                ->setPublicationType($this->getAttr($refEle->filter('element-citation'), 'publication-type'))
                ->setArticleTitle($this->getBySelector($refEle, 'element-citation > article-title', 'text'))
                ->setSource($this->getBySelector($refEle, 'element-citation > source', 'text'))
                ->setYear($this->getBySelector($refEle, 'element-citation > year', 'text'))
                ->setVolume($this->getBySelector($refEle, 'element-citation > volume', 'text'))
                ->setFPage($this->getBySelector($refEle, 'element-citation > fpage', 'text'))
                ->setLPage($this->getBySelector($refEle, 'element-citation > lpage', 'text'))
                ->setDoi($this->getBySelector($refEle, 'element-citation > pub-id[pub-id-type="doi"]', 'text'))
                ->setPublicationType($this->getBySelector($refEle, 'element-citation > publisher-loc', 'text'))
                ->setPublisher($this->getBySelector($refEle, 'element-citation > publisher-name', 'text'))
            ;
            $refEle->filter('element-citation > person-group[person-group-type="author"] > name')
                ->each(function(Crawler $authorEle) use ($ref) {
                    $author = new Author();
                    $author
                        ->setGivenName($this->getBySelector($authorEle, 'given-names', 'text'))
                        ->setSurname($this->getBySelector($authorEle, 'surname', 'text'))
                    ;

                    $ref->addPerson($author);
                }
            );
            $refEle->filter('element-citation > person-group[person-group-type="editor"] > name')
                ->each(function(Crawler $authorEle) use ($ref) {
                    $editor = new Author();
                    $editor
                        ->setGivenName($this->getBySelector($authorEle, 'given-names', 'text'))
                        ->setSurname($this->getBySelector($authorEle, 'surname', 'text'))
                    ;

                    $ref->addEditor($editor);
                }
            );
        });
    }

    private function processBackNote(Crawler $crawler)
    {
        $tag = $crawler->nodeName();
        $backItem = new BackItem();
        $this->article->addBackItem($backItem);

        $backItemType = $tag;
        foreach (BackItem::$types as $backType) {
            if (stripos($tag, $backType) !== false) {
                $backItemType = $backType;
            }
        }

        if ($crawler->filter('title')->count() > 0) {
            $crawler = $crawler->filter('title')->parents()->eq(0);
        }

        $backItem
            ->setType($backItemType)
            ->setTitle($this->getBySelector($crawler, 'title', 'text'))
            ->setId($this->formatId($this->getAttr($crawler, 'id')))
            ->setAttrType($this->getAttr($crawler, 'notes-type'))
        ;
        $crawler->children()->each(function (Crawler $child) use ($backItem) {
            $tag = $child->nodeName();
            if ($tag != 'title') {
                if ($tag == 'p') {
                    $type = Content::TYPE_TEXT;
                    $info = $this->getBySelector($child);
                } else {
                    $type = 'text';
                    foreach(Content::$types as $contentType) {
                        if (stripos($tag, $contentType) !== false) {
                            $type = $contentType;
                        }
                    }

                    $info = trim($child->outerHtml());
                }
                $content = new Content();
                $content->setType($type)->setInfo($info);
                $backItem->addContent($content);
            }

        });
    }

    private function processSections(Crawler $crawler)
    {
        $getSection = function(Crawler $secEle) {
            $section = new Section();
            $section
                ->setType($this->getAttr($secEle, 'sec-type'))
                ->setId($this->formatId($this->getAttr($secEle, 'id')))
                ->setTitle($this->getBySelector($secEle, 'title', 'text'))
                ->setArticle($this->article)
            ;

            $secEle->children('p')->each(function(Crawler $p) use ($section) {
                $content = new Content();
                $content->setType(Content::TYPE_TEXT)->setInfo($this->getBySelector($p));
                $section->addContent($content);
            });
            return $section;
        };
        $crawler->children('sec')->each(function(Crawler $sec) use ($getSection) {
            $section = $getSection($sec);
            $this->article->addSection($section);
            $sec->children('sec')->each(function (Crawler $subSec) use ($getSection, $section) {
                $subSection = $getSection($subSec);
                $subSection->setParent($section);
                $section->addChild($subSection);

                $subSec->children('sec')->each(function (Crawler $thirdSec) use ($getSection, $subSection) {
                    $thirdSection = new Section();
                    $thirdSection->setParent($subSection);
                    $subSection->addChild($thirdSection);
                });
            });
        });
    }

    private function formatId(?string $id)
    {
        if (empty($id)) {
            return null;
        }

        return $id;
//        return str_replace($this->article->getId(), '', $id);
    }

    private function processCategory(Crawler $crawler)
    {
        $subjects = $crawler->filter('article-categories subj-group')->each(function (Crawler $subj) {
            return $this->getBySelector($subj, 'subject', 'text');
        });

        $this->article->setCategories($subjects);
    }

    private function processAuthors(Crawler $crawler)
    {
        $crawler->filter('contrib-group contrib[contrib-type="author"]')->each(function(Crawler $authorEle) {
            $author = new Author();
            $author
                ->setSurname($this->getBySelector($authorEle, 'name > surname', 'text'))
                ->setGivenName($this->getBySelector($authorEle, 'name > given-names', 'text'))
                ->setAffs(implode(',', $authorEle->filter('xref')->each(function (Crawler $xref) {
                    return trim($xref->text());
                })))
                ->setOrcid($this->formatOrcid($this->getBySelector($authorEle, 'contrib-id[contrib-id-type="orcid"]', 'text')))
            ;
            $this->article->addAuthor($author);
        });
    }

    private function processEditors(Crawler $crawler)
    {
        $crawler->filter('contrib-group contrib[contrib-type="editor"]')->each(function(Crawler $editorEle) {
            $editor = new Editor();
            $editor
                ->setSurname($this->getBySelector($editorEle, 'name > surname', 'text'))
                ->setGivenName($this->getBySelector($editorEle, 'name > given-names', 'text'))
                ->setRole($this->getBySelector($editorEle, 'role', 'text'))
            ;

            $this->article->addEditor($editor);
        });
    }

    private function processAffiliation(Crawler $crawler)
    {
        $crawler->filter('aff')->each(function (Crawler $affEle) {
            $aff = new Affiliation();
            $affString = $affEle->html();
            list($label, $info) = $this->formatAff($affString);
            $aff->setLabel($label)->setContent($info)->setOrderNumber($label);
            $this->article->addAffiliation($aff);
        });

        $authorNotes = $crawler->filter('author-notes');
        if ($authorNotes->count() > 0) {
            $authorNotes->children()->each(function (Crawler $note) {
                $aff = new Affiliation();
                $affString = $note->html();
                list($label, $info) = $this->formatAff($affString);
                $aff->setLabel($label)->setContent($info);

                $this->article->addAffiliation($aff);
            });
        }
    }

    private function processDates(Crawler $crawler)
    {
        $getDate = function (Crawler $crawler)
        {
            if ($crawler->count() == 0) {
                return null;
            }
            $year = $this->getBySelector($crawler, 'year', 'text');
            if (strlen($year) !== 4) {
                return null;
            }

            $month = $this->getBySelector($crawler, 'month', 'text') ?: '00';
            $day = $this->getBySelector($crawler, 'day', 'text') ?: '00';

            return \Datetime::createFromFormat('Y-m-d', $year . '-' . $month . '-' . $day);
        };

        $this->article
            ->setPublishedDate($getDate($crawler->filter('pub-date[pub-type="epub"]')))
            ->setPrintDate($getDate($crawler->filter('pub-date[pub-type="ppub"]')))
            ->setReceivedDate($getDate($crawler->filter('history > date[date-type="received"]')))
            ->setAcceptedDate($getDate($crawler->filter('history > date[date-type="accepted"]')))
        ;
    }

    private function processPermissions(Crawler $crawler)
    {
        $permission = new Permission();
        $permission
            ->setStatement($this->getBySelector($crawler, 'permissions > copyright-statement', 'text'))
            ->setYear($this->getBySelector($crawler, 'permissions > copyright-year', 'text'))
            ->setLicenseType($this->getAttr($crawler->filter('permissions > license'), 'license-type'))
            ->setLicenseContents($crawler->filter('permissions > license > license-p')->each(function (Crawler $lp) {
                return trim($lp->html());
            }))
        ;

        $this->article->setPermission($permission);
    }

    private function getBySelector(Crawler $crawler, ?string $selector=null, string $type='html')
    {
        $filter = empty($selector) ? $crawler : $crawler->filter($selector);
        if ($filter->count() > 0) {
            return trim($filter->$type());
        }

        return null;
    }

    private function getAttr(Crawler $crawler, string $attrName)
    {
        if ($crawler->count() > 0) {
            return trim($crawler->attr($attrName));
        }
        return null;
    }

    private function formatOrcid(?string $orcid)
    {
        if (preg_match('/\/?([\d-]+)\s*$/', $orcid, $matches)) {
            return $matches[1];
        }

        return $orcid;
    }

    private function formatAff(?string $aff)
    {
        if (preg_match('/<label>(.*?)<\/label>\s*(.*)$/', $this->formatHtmlString($aff), $matches)) {
            return [$matches[1], preg_replace('/(^<p>|<\/p>$)/', '', $matches[2])];
        }

        return ['', $aff];
    }

    private function formatHtmlString(?string $string)
    {
        $string = trim(str_replace("\n", '', $string));
        $string = preg_replace('/(^<p>|<\/p>$)/', '', $string);
        return $this->formatter->formatString($string);
    }

}
