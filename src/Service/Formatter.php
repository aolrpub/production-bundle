<?php

namespace Aolr\ProductionBundle\Service;

class Formatter
{
    public function formatType(?string $type)
    {
        if (empty($type)) {
            return null;
        }

        return trim($type);
    }

    public function formatTitle(?string $title)
    {
        if (empty($title)) {
            return null;
        }

        return trim($title);
    }

    public function formatNoteString(string $string)
    {
        // remove img tag
        return preg_replace('/<img[\s\S]*?>/', '', $string);
    }

    public function formatDoi(string $string)
    {
        if (preg_match('/(10\.\d+\/.*)\s*/', $string, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function formatAbstract(?string $string)
    {
        if (empty($string)) {
            return null;
        }

        return preg_replace(['/^<b>\s*Abstract:?\s*<\/b>/i', '/^Abstract:?\s*/'], ['', ''], $string);
    }

    public function formatKeywords(?string $string)
    {
        if (empty($string)) {
            return null;
        }
        return preg_replace(['/^<b>\s*Keywords:?\s*<\/b>/i', '/^Keywords:?\s*/'], ['', ''], $this->formatString($string));
    }

    public function formatString(?string $string)
    {
        if (empty($string)) {
            return null;
        }

        return trim(preg_replace(['/\s+/', '/<[^\/>]+>(\s*)<\/[^>]+>/'], [' ', '$1'], $string));
    }

    public function formatTable(string $string)
    {
        return preg_replace_callback('/<p.*?>(.*)<\/p>/', function ($matches) {
            return $this->formatString($matches[1]);
        }, $string);
    }

}
