<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class OKX implements ExchangeInterface
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
        $url = $this->baseUrl . '/api/v5/market/tickers?instType=SPOT';

        return $this->httpClient->get($url)->then(function ($data) {

            return array_map(function ($market) {
                list($base, $quote) = explode("-", $market->instId);
                $symbol = $base . $quote;
                return new StandardMarketPair($symbol, $base, $quote, $market->vol24h, $market->volCcy24h, $market->askPx);
            }, $data->data);

        });
    }
}