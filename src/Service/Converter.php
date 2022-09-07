<?php

namespace Aolr\ProductionBundle\Service;

use Aolr\ProductionBundle\Entity\Article;

class Converter
{
    const TYPE_MS_WORD      = 1;
    const TYPE_PDF          = 2;
    const TYPE_JATS         = 3;
    const TYPE_PMC          = 4;
    const TYPE_CROSSREF     = 5;
    const TYPE_SCIELO       = 6;
    const TYPE_REDALYC      = 7;
    const TYPE_DATACITE     = 8;
    const TYPE_DOAJ         = 9;
    const TYPE_RICH_HTML    = 10;

    /**
     * @var WordParser
     */
    private $wordParser;

    /**
     * @var JATSParser
     */
    private $jatsParser;

    /**
     * @var TemplateRender
     */
    private $templateRender;

    public function __construct(WordParser $wordParser, JATSParser $jatsParser, TemplateRender $templateRender)
    {
        $this->wordParser = $wordParser;
        $this->jatsParser = $jatsParser;
        $this->templateRender = $templateRender;
    }

    public function getResult($path, int $fromType, int $toType, $article = null)
    {
        if (empty($article) || !$article instanceof Article) {
            $article = $this->getArticleObject($path, $fromType);
        }
        $methodName = $this->getTypeName($toType);
        return $this->templateRender->$methodName($article);
    }

    /**
     * @throws \Exception
     */
    public function getArticleObject($path, int $type): Article
    {
        $parser = $this->getParser($type);

        if ($parser instanceof ParserInterface) {
            return $parser->parse($path);
        }

        throw new \Exception('Can not found parser for type ' . $type);
    }

    public function getParser($type)
    {
        switch ($type) {
            case self::TYPE_MS_WORD:
                $parser = $this->wordParser;
                break;
            case self::TYPE_JATS:
                $parser = $this->jatsParser;
                break;
            default:
                throw new \Exception('illegal type "' . $type . '"');
        }

        return $parser;
    }

    private function getOutputPath(string $inputPath, int $toType)
    {
        $baseDir = pathinfo($inputPath, PATHINFO_DIRNAME);
        if (!file_exists($baseDir . '/tmp')) {
            mkdir($baseDir . '/tmp', 0777, true);
        }

        if ($toType == self::TYPE_PDF) {
            $extension = 'pdf';
        } else if ($toType == self::TYPE_RICH_HTML) {
            $extension = 'html';
        } else {
            $extension = 'xml';
        }

        $fileName = $this->getTypeName($toType) . '.' . $extension;

        $baseDir = pathinfo($inputPath, PATHINFO_DIRNAME);
        return $baseDir . '/tmp/' . $fileName;
    }

    private function getTypeName(int $type)
    {
        $objClass = new \ReflectionClass(__CLASS__);
        $constants = array_flip($objClass->getConstants());
        return strtolower(str_replace(['TYPE_', '_'], '', $constants[$type]));
    }

}
