<?php

namespace App\Exchanges;

use App\ExchangeInterface;
use App\HttpClient;
use React\Promise\PromiseInterface;
use App\StandardMarketPair;

class UniswapV3 implements ExchangeInterface
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
    $poolsPerRequest = 1000;
    $totalPools = 6000;
    $requests = [];

    for ($i = 0; $i < $totalPools; $i += $poolsPerRequest) {
      $query = '
        {
          pools(first: ' . $poolsPerRequest . ', skip: ' . $i . ', orderBy: txCount, orderDirection: desc) {
            id
            token0 {
              name
              id
              symbol
              decimals
            }
            token1 {
              name
              id
              symbol
              decimals
            }
            feeTier
            liquidity
            token0Price
            token1Price
            volumeToken0
            volumeToken1
            txCount
          }
        }';

      $requests[] = $this->httpClient->postJson($this->baseUrl, ['query' => $query]);
    }

    return \React\Promise\all($requests)->then(function (array $responses) {
      $pools = [];
      foreach ($responses as $response) {
        foreach ($response->data->pools as $pool) {
          $token0 = $pool->token0->symbol;
          $token1 = $pool->token1->symbol;
          if (in_array($token0, STABLECOINS) && !in_array($token1, STABLECOINS)) {
            list($token0, $token1) = [$token1, $token0];
            list($pool->volumeToken0, $pool->volumeToken1) = [$pool->volumeToken1, $pool->volumeToken0];
            list($pool->token0, $pool->token1) = [$pool->token1, $pool->token0];
            list($pool->token0Price, $pool->token1Price) = [$pool->token1Price, $pool->token0Price];
          }
          $pair = $token0 . $token1;
          if (array_key_exists($pair, $pools)) {
            $pools[$pair]->volumeToken0 += $pool->volumeToken0;
            $pools[$pair]->volumeToken1 += $pool->volumeToken1;
            $pools[$pair]->liquidity += $pool->liquidity;
          } else {
            $pools[$pair] = $pool;
          }
        }
      }
      return array_map(function ($pool) {

        $base = $pool->token0->symbol;
        $quote = $pool->token1->symbol;
        $symbol = $base . $quote;

        return new StandardMarketPair($symbol, $base, $quote, $pool->volumeToken0, $pool->volumeToken1, $pool->token1Price);
      }, $pools);
    });
  }
}