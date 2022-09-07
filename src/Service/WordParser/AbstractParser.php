<?php

namespace Aolr\ProductionBundle\Service\WordParser;

use Aolr\ProductionBundle\Entity\Article;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractParser implements WordParserInterface
{
    /**
     * @var Article
     */
    protected $article;

    /**
     * @var Crawler
     */
    protected $crawler;

    public function __construct()
    {
        $this->article = new Article();
    }

    public function setCrawler(Crawler $crawler)
    {
        $this->crawler = $crawler;
        return $this;
    }
}
