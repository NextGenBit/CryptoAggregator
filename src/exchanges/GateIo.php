<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class GateIo implements ExchangeInterface
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
        $url = $this->baseUrl . '/api/v4/spot/tickers';
        return $this->httpClient->get($url)->then(function ($data) {


            return array_map(function ($pair) {

                list($base,$quote) = explode("_",$pair->currency_pair);

                $symbol = $base . $quote;
    
                return new StandardMarketPair($symbol, $base, $quote, $pair->base_volume, $pair->quote_volume, $pair->lowest_ask);
            }, $data);
        });
    }
}