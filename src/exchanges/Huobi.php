<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class Huobi implements ExchangeInterface
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

        $url = $this->baseUrl . '/v1/common/symbols';
        return $this->httpClient->get($url)->then(function ($response) {

            $symbols = [];
            foreach($response->data as $market) {
                $last2type = substr($market->{'base-currency'}, -2);

                if ($last2type == "3l" || $last2type == "3s") {
                    continue;
                }

                $symbols[strtoupper($market->symbol)] = ["base" => strtoupper($market->{'base-currency'}), "quote" => strtoupper($market->{'quote-currency'})];
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
            if($this->symbolsData != $symbolsData) {
                $this->symbolsData = $symbolsData;
            }
            $tickerUrl = $this->baseUrl . '/market/tickers';
            return $this->httpClient->get($tickerUrl)->then(function ($tickerData) {
                return array_map(function ($market)  {

                    $symbol = strtoupper($market->symbol);

                    if(!isset($this->symbolsData[$symbol])) {
                        return;
                    }

                    $base = $this->symbolsData[$symbol]["base"];
                    $quote = $this->symbolsData[$symbol]["quote"];


                    return new StandardMarketPair($symbol, $base, $quote, $market->amount, $market->vol, $market->ask);
                }, $tickerData->data);
            });
        });
    }
    
}