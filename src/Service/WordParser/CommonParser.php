<?php

namespace Aolr\ProductionBundle\Service\WordParser;


use Aolr\ProductionBundle\Entity\Article;

class CommonParser extends AbstractParser
{

    public function getData(): Article
    {

        return $this->article;
    }

}
