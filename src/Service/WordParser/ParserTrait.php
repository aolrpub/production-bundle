<?php

namespace Aolr\ProductionBundle\Service\WordParser;

use Aolr\ProductionBundle\Entity\Section;
use Symfony\Component\DomCrawler\Crawler;

trait ParserTrait
{
    public function isType(Crawler $crawler, int $index): bool
    {
        $text = trim($crawler->text());
        return strlen($text) < 15 && ($index === 0  || preg_match('/(article|book|chapter|paper|conference|report)/', $text));
    }

    public function isTitle(Crawler $prevElement, int $index): bool
    {
        return $this->isType($prevElement, $index - 1);
    }

    public function isAuthor(Crawler $crawler, int $index): bool
    {
        if ($index > 5) {
            return false;
        }
        $text = $crawler->text();
        $authors = preg_split('/(,\s|\sand\s)/', $text);

        return !empty($authors) && preg_match('/[\w-.]{2,10}\s[\w-.]{2,10}/', $authors[0]);
    }

    public function isAffiliation(Crawler $crawler, int $index): bool
    {
        if ($index > 20) {
            return false;
        }
        $text = trim($crawler->text());

        return preg_match('/^(\d|\*|†)\s+/', $text, $matches);
    }

    public function isAbstract(Crawler $crawler): bool
    {
        return preg_match('/^Abstract:/i', trim($crawler->text()));
    }

    public function isKeywords(Crawler $crawler): bool
    {
        return preg_match('/^Keywords:/i', trim($crawler->text()));
    }

    public function isSectionHead(Crawler $crawler): bool
    {
        return preg_match('/^(\d+\.)+/', trim($crawler->text()));
    }

    public function isSectionText(Crawler $crawler): bool
    {
        return $this->currentContainer instanceof Section && !$this->isSectionHead($crawler) && !$this->isBackMatter($crawler);
    }

    public function isBackMatter(Crawler $crawler): bool
    {
        if (empty($this->indexes['section']) || !empty($this->indexes['references'])) {
            return false;
        }

        if (preg_match('/(Supplementary|Contribution|Funding|Statement|Conflict).{0,20}:/i', $this->formatter->formatString($crawler->text()), $matches)) {
            return !in_array(strtolower($matches[1]), ['abstract', 'keywords', 'keyword']);
        }

        return false;

    }

    public function isAppendix(Crawler $crawler): bool
    {
        return preg_match('/^Appendix/i', trim($crawler->text()));
    }

    public function isFigure(Crawler $crawler): bool
    {
        return !$this->isTable($crawler) && $crawler->filter('img')->count() > 0;
    }

    public function isReferenceTitle(Crawler $crawler): bool
    {
        $rawText = trim(strtolower($crawler->text()));

        return in_array($rawText, ['reference', 'references']);
    }

    public function isReference(): bool
    {
        if ($this->currentContainer == 'references') {
            return true;
        }

//        $rawText = $this->formatter->formatString($crawler->text());
//        if (preg_match('/\d{4},\d+/', $rawText) && preg_match('/\d+–\d+/', $rawText)) {
//            return true;
//        }

        return false;
    }

    public function isTable(Crawler $crawler): bool
    {
        return strtolower($crawler->nodeName()) == 'table';
    }

    public function isFormula(Crawler $crawler): bool
    {
        return $this->isTable($crawler) && preg_match('/&lt;math/', $crawler->html());
    }

    public function isCaption(Crawler $crawler): bool
    {
        return preg_match('/^(Table|Figure)\s+\S+?\.?\s+/i', trim($crawler->text()));
    }

    /**
     * Should put it at latest position
     * @param Crawler $crawler
     *
     * @return bool
     */
    public function isTableFooter(Crawler $crawler, Crawler $prevElement): bool
    {
        if ($this->isTable($prevElement)) {
            $classes = explode(' ', $crawler->attr('class'));
            if (stripos($classes[0] ?? '', 'footer') !== false) {
                return true;
            }

            if (!preg_match('/^<b[^>]*>/i', $crawler->text()) && !empty($crawler->attr('algin'))) {
                return true;
            }
        }

        return false;
    }

    public function hasResource(Crawler $crawler)
    {
        return $crawler->filter('img')->count() > 0 || $crawler->filter('table')->count() > 0;
    }
}
