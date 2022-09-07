<?php

namespace Aolr\ProductionBundle\Service\WordParser;

use Aolr\ProductionBundle\Entity\Article;
use Symfony\Component\DomCrawler\Crawler;

interface WordParserInterface
{
    public function setCrawler(Crawler $crawler);
    public function getData() :Article;
}
