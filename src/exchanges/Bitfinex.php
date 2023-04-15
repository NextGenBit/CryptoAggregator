<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class Bitfinex implements ExchangeInterface
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
        $url = $this->baseUrl . '/v2/tickers?symbols=ALL';
        return $this->httpClient->get($url)->then(function ($data) {
            $mappedData =  array_map(function ($market) {
                $type = substr($market[0], 0, 1);
                if ($type != 't') {
                    return false;
                }
                $pair = substr($market[0], 1);
                if (strpos($pair, ":") !== false) {
                    list($base, $quote) = explode(":", $pair);
                } else {
                    list($base, $quote) = str_split($pair, 3);
                }
                $symbol = $base . $quote;
                

                return new StandardMarketPair($symbol, $base, $quote, $market[8], NULL, $market[3]);
            }, $data);

            return array_filter($mappedData);
        });
    }
}