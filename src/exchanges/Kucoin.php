<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class Kucoin implements ExchangeInterface
{
    private $httpClient;
    private $baseUrl;

    public function __construct(HttpClient $httpClient, array $config)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $config['base_url'];
    }

    public function fetchMarketPairs(): PromiseInterface
    {
        $url = $this->baseUrl . '/api/v1/market/allTickers';
        return $this->httpClient->get($url)->then(function ($data) {

            return array_map(function ($market) {

                list($base, $quote) = explode("-", $market->symbol);
                $symbol = $base . $quote;
                return new StandardMarketPair($symbol, $base, $quote, $market->vol, $market->volValue, $market->sell);
            }, $data->data->ticker);
        });
    }
}