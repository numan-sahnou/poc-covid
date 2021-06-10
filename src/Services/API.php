<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class API{

    private $client;

    /**
     * HomeController constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getFranceData(): array{
        return $this->getApi('FranceLiveGlobalData');
    }

    public function getAllData(): array{
        return $this->getApi('AllLiveData');
    }

    public function getDateData($date): array{
        return $this->getApi('AllDataByDate?date=' . $date);
    }

    public function getApi(string $var): array {
        try {
            $response = $this->client->request(
                'GET',
                'https://coronavirusapi-france.now.sh/' . $var
            );
        } catch (TransportExceptionInterface $e) {
            return $e->getCode();
        }

        return $response->toArray();
    }
}