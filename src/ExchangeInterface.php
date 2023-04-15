<?php

namespace App;

use React\Promise\PromiseInterface;

interface ExchangeInterface
{
    public function fetchMarketPairs(): PromiseInterface;
}
