<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class Binance implements ExchangeInterface
{
    private $httpClient;
    private $baseUrl;
    private $symbolsData = null;
    private $symbolDataTime = null;
    private $updateInfoThreshold = null;

    public function __construct(HttpClient $httpClient, array $config)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $config['base_url'];
        $this->updateInfoThreshold = $config['updateInfo'];
    }

    private function fetchSymbolsData(): PromiseInterface
    {
        $this->symbolDataTime = time();
        $url = $this->baseUrl . '/api/v3/exchangeInfo';
        return $this->httpClient->get($url)->then(function ($response) {
            $symbols = [];
            foreach ($response->symbols as $market) {
                $symbols[$market->symbol] = ["base" => $market->baseAsset, "quote" => $market->quoteAsset];
            }
            return $symbols;
        });
    }

    public function fetchMarketPairs(): PromiseInterface
    {
        $timeElapsed = (time() - $this->symbolDataTime) / 60;
        if ($this->symbolsData === null || $timeElapsed > $this->updateInfoThreshold) {
            $fetchSymbolsPromise = $this->fetchSymbolsData();
        } else {
            $fetchSymbolsPromise = \React\Promise\resolve($this->symbolsData);
        }

        return $fetchSymbolsPromise->then(function ($symbolsData) {
            if ($this->symbolsData != $symbolsData) {
                $this->symbolsData = $symbolsData;
            }
            $tickerUrl = $this->baseUrl . '/api/v3/ticker/24hr';
            return $this->httpClient->get($tickerUrl)->then(function ($tickerData) {
                $mappedData = array_map(function ($market) use (&$symbolToTickerData) {
                    if (!isset($this->symbolsData[$market->symbol])) {
                        return false;
                    }
                    $symbol = $market->symbol;
                    $base = $this->symbolsData[$market->symbol]["base"];
                    $quote = $this->symbolsData[$market->symbol]["quote"];

                    return new StandardMarketPair($symbol, $base, $quote, $market->volume, $market->quoteVolume, $market->askPrice);
                }, $tickerData);

                return array_filter($mappedData);
            });
        });
    }

}