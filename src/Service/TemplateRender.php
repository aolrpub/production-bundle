<?php

namespace Aolr\ProductionBundle\Service;

use Aolr\ProductionBundle\Entity\Article;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class TemplateRender
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function JATS(Article $article)
    {
        $string = $this->twig->render('@AolrProduction/jats.xml.twig', [
            'article' => $article
        ]);

        if ($article->getId() != Article::DEFAULT_ID) {
            $string = str_replace(Article::DEFAULT_ID, $article->getId(), $string);
        }

        $doc = new \DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($string);
        return $doc->saveXML();
    }

    public function richHtml(Article $article)
    {

    }

    public function crossref(Article $article, $options=[])
    {
        $string = $this->twig->render('@AolrProduction/crossref.xml.twig', [
            'article' => $article,
            'options' => $options
        ]);

        $doc = new \DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($string);
        return $doc->saveXML();
    }

    /**
     * @param Article $article
     *
     * @return Response
     */
    public function downloadJATS(Article $article)
    {
        $string = $this->JATS($article);
        $response = new Response($string);
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'article_jats.xml');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');

        return $response;
    }

    public function generateResponse(Article $article, int $type)
    {
        switch ($type) {
            case Converter::TYPE_JATS:
                $params = [
                    'file_name' => 'article_jats.xml',
                    'type' => 'text/xml',
                    'content' => $this->JATS($article)
                ];
                break;

            case Converter::TYPE_CROSSREF:
                $params = [
                    'file_name' => 'article_crossref.xml',
                    'type' => 'text/xml',
                    'content' => $this->crossref($article)
                ];
                break;

            default:
                throw new \Exception('wrong type "' . $type . '" using.');
        }

        $response = new Response($params['content']);
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $params['file_name']);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $params['type'] . '; charset=utf-8');

        return $response;
    }
}
