<?php

namespace Aolr\ProductionBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\CoreExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{

    /**
     * @var Environment
     */
    private $twig;
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('date', [$this, 'dateFilter'])
        ];
    }


    public function dateFilter($date, $format = 'Y-m-d H:i:s')
    {
        if (!empty($date)) {
            return twig_date_format_filter($this->twig, $date, $format);
        }

        return null;
    }
}
