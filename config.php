<?php

define('STABLECOINS',['USDC','USDT','BUSD','DAI']);

return [
    'http_client' => [
        'timeout' => 30
    ],
    'exchanges' => [
        'Binance' => [
            'enabled' => false,
            'base_url' => 'https://api.binance.com',
            'updateInfo' => 60
        ],
        'Bitfinex' => [
            'enabled' => false,
            'base_url' => 'https://api-pub.bitfinex.com'
        ],
        'GateIo' => [
            'enabled' => false,
            'base_url' => 'https://api.gateio.ws'
        ],
        'Bitmart' => [
            'enabled' => false,
            'base_url' => 'https://api-cloud.bitmart.com'
        ],
        'Kucoin' => [
            'enabled' => false,
            'base_url' => 'https://api.kucoin.com'
        ],
        'Bitstamp' => [
            'enabled' => false,
            'base_url' => 'https://www.bitstamp.net'
        ],
        'Poloniex' => [
            'enabled' => false,
            'base_url' => 'https://api.poloniex.com'
        ],
        'Huobi' => [
            'enabled' => false,
            'base_url' => 'https://api.huobi.pro',
            'updateInfo' => 60
        ],
        #DEX
        'UniswapV2' => [
            'enabled' => true,
            'base_url' => 'https://api.thegraph.com/subgraphs/name/uniswap/uniswap-v2'
        ],
        'UniswapV3' => [
            'enabled' => true,
            'base_url' => 'https://api.thegraph.com/subgraphs/name/uniswap/uniswap-v3'
        ],
        'UniswapV3ARB' => [
            'enabled' => false,
            'basedOn' => 'UniswapV3',
            'base_url' => 'https://api.thegraph.com/subgraphs/name/ianlapham/arbitrum-minimal'
        ],
        'PancakeswapV3BSC' => [
            'enabled' => false,
            'basedOn' => 'UniswapV3',
            'base_url' => 'https://api.thegraph.com/subgraphs/name/pancakeswap/exchange-v3-bsc'
        ],   
        'PancakeswapV3ETH' => [
            'enabled' => false,
            'basedOn' => 'UniswapV3',
            'base_url' => 'https://api.thegraph.com/subgraphs/name/pancakeswap/exchange-v3-eth'
        ],    

    ]
];