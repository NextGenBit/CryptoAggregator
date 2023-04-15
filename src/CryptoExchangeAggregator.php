<?php

namespace App;

use React\EventLoop\LoopInterface;
use React\Promise;
use React\Promise\PromiseInterface;

class CryptoExchangeAggregator
{
    private $loop;
    private $httpClient;
    private $exchanges;
    private $startTime;

    public function __construct(LoopInterface $loop, HttpClient $httpClient, array $config)
    {
        $this->loop = $loop;
        $this->httpClient = $httpClient;
        $this->exchanges = [];
    }

    public function addExchange(string $exchangeName, ExchangeInterface $exchange)
    {
        $this->exchanges[$exchangeName] = $exchange;
    }

    public function fetchAllMarketPairs(): PromiseInterface
    {
        $promises = [];
        foreach ($this->exchanges as $exchangeName => $exchange) {
            $promises[$exchangeName] = $exchange->fetchMarketPairs();
        }
        return Promise\all($promises);
    }

    public function run()
    {
        $this->loop->run();
    }
}
