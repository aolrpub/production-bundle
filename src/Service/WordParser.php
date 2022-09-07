<?php

namespace Aolr\ProductionBundle\Service;

use Aolr\ProductionBundle\Entity\Article;
use Aolr\ProductionBundle\Service\WordParser\CommonParser;
use Aolr\ProductionBundle\Service\WordParser\MDPIParser;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Writer\HTML;
use Symfony\Component\DomCrawler\Crawler;

class WordParser implements ParserInterface
{
    /**
     * @var MDPIParser
     */
    private $mdpiParser;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(MDPIParser $mdpiParser, Formatter $formatter)
    {
        $this->mdpiParser = $mdpiParser;
        $this->formatter = $formatter;
    }

    public function exportHtmlFile(string $filePath, string $outputFile)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File "' . $filePath . '" does not exist.');
        }

        Settings::setOutputEscapingEnabled(true);
        $phpWord = IOFactory::load($filePath);

        file_put_contents($outputFile, $this->getHtmlString($phpWord));
        return $outputFile;
    }

    private function getHeaderAndFooter(PHPWord $phpWord)
    {
        $getNoteArray = function ($notes) {
            return array_values(array_filter(array_map(function($note) {
                $writer = new HTML();
                $container = new HTML\Element\Container($writer, $note, true);

                return $this->formatter->formatNoteString($container->write());
            }, $notes)));
        };
        $sections = $phpWord->getSections();
        $headerStrings = [];
        $footerStrings = [];
        foreach ($sections as $section) {
            $footers = $section->getfooters();
            $headers = $section->getheaders();
            $headerStrings = array_merge($headerStrings, $getNoteArray($headers));
            $footerStrings = array_merge($footerStrings, $getNoteArray($footers));
        }

        return [implode(PHP_EOL, $headerStrings), implode(PHP_EOL, $footerStrings)];
    }

    public function getHtmlString(PhpWord $phpWord)
    {
//        $p = $phpWord->getSection(0)->getElement(86);
//        dump($p->getRows()[1]->getCells());exit;

        $phpWord->getSettings()->setDoNotHyphenateCaps(true);
        $phpWord->getSettings()->setDoNotTrackFormatting(true);

        list($header, $footer) = $this->getHeaderAndFooter($phpWord);

        $htmlWriter = new HTML($phpWord);
        $part = new HTML\Part\Body();
        $part->setParentWriter($htmlWriter);

        $body = $part->write();
        $header = "<header>" . PHP_EOL . "{$header}</header>";
        $footer = "<footer>" . PHP_EOL . "{$footer}</footer>";
        $body = str_replace(["<body>", "</body>"], ["<article>", "</article>"], $body);
        $content = "<html lang=\"en\"><head><title></title></head>" . PHP_EOL;
        $content .= "<body>" . PHP_EOL . "{$header}" . PHP_EOL;
        $content .= "{$body}" . PHP_EOL . "{$footer}" . PHP_EOL;
        $content .= "</body></html>";
        return $content;
    }

    public function parse(string $wordPath): Article
    {
        if (!file_exists($wordPath)) {
            throw new \Exception('no word file found: "' . $wordPath . '"');
        }

        $baseDir = pathinfo($wordPath, PATHINFO_DIRNAME);
        if (!file_exists($baseDir . '/tmp')) {
            mkdir($baseDir . '/tmp', 0777, true);
        }

        $baseDir = pathinfo($wordPath, PATHINFO_DIRNAME);
        $outFilePath = $baseDir . '/tmp/exported.html';

        $this->exportHtmlFile($wordPath, $outFilePath);
        return $this->parseHtml($outFilePath);
    }

    /**
     * @throws \Exception
     */
    public function parseHtml(string $htmlFilePath)
    {
        if (!file_exists($htmlFilePath)) {
            throw new \Exception('file not found: ' . $htmlFilePath);
        }

        $htmlString = $this->formatHtml(file_get_contents($htmlFilePath));
        $crawler = new Crawler();
        $crawler->addHtmlContent($htmlString);

        $parser = $this->getParser($crawler);

        return $parser->setCrawler($crawler)->getData();

    }

    public function getParser(Crawler $crawler)
    {
        $body = $crawler->filter('body');
        if ($body->count() > 0) {
            if (preg_match('/class="MDPI\S+/i', $body->html())) {
                return $this->mdpiParser;
            }
        }

        return new CommonParser($crawler);
    }

    private function formatHtml(?string $htmlString)
    {
        if (empty($htmlString)) {
            return '';
        }

        $htmlString = str_replace(["\n", "&nbsp;", " "], [' ', ' ', ''], $htmlString);
        $htmlString = preg_replace(['/(<div[\s\S]*?>|<\/div>|<span[\s\S]*?>|<\/span>)|<br[\s\S]*?>/'], [''], $htmlString);

        $htmlString = preg_replace_callback('/<a([^\/>]*)>(.*?)<\/a>/', function ($matches) {
            if (!empty($matches[1]) && $matches[1][0] !== '') {
                return $matches[0];
            }
            if (stripos($matches[1], 'href') === false) {
                return $matches[2];
            }
            return $matches[0];
        }, $htmlString);

        $htmlString = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $htmlString);

        return $this->formatter->formatString($htmlString);
    }
}
