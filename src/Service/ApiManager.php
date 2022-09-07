<?php

namespace Aolr\ProductionBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ApiManager
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $enableROR;

    public function __construct(LoggerInterface $logger, bool $enableROR)
    {
        $this->logger = $logger;
        $this->enableROR = $enableROR;
    }

    public function getRorId(?string $affText)
    {
        if (empty($affText) || !$this->enableROR) {
            return null;
        }
        $url = 'https://api.ror.org/organizations?affiliation=' . $affText;
        try {
            $client = new Client();
            $response = $client->request('GET', $url);
            $data = json_decode($response->getBody()->getContents(), true);
            $chosen = array_filter($data['items'] ?? [], function ($item) {
                return $item['chosen'];
            });

            return $chosen[0]['organization']['id'] ?? '';
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return null;
    }
}
