<?php

namespace Aolr\ProductionBundle\Service;

use Aolr\ProductionBundle\Entity\Article;

interface ParserInterface
{
    public function parse(string $filePath) : Article;
}
