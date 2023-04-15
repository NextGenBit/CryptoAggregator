<?php

require 'vendor/autoload.php';

use App\CryptoExchangeAggregator;
use App\HttpClient;

$config = require 'config.php';
$loop = React\EventLoop\Factory::create();
$httpClient = new HttpClient($loop, $config['http_client']);

$aggregator = new CryptoExchangeAggregator($loop, $httpClient, $config);
foreach ($config['exchanges'] as $exchangeName => $exchangeData) {
    if ($exchangeData['enabled']) {

        if (!isset($exchangeData["basedOn"])) {
            $className = 'App\\Exchanges\\' . $exchangeName;
        } else {
            $className = 'App\\Exchanges\\' . $exchangeData["basedOn"];
        }
        if (class_exists($className)) {
            $aggregator->addExchange($exchangeName, new $className($httpClient, $exchangeData));
        } else {
            echo "Warning: Exchange class '{$className}' not found. Skipping...\n";
        }
    }
}


$aggregator->fetchAllMarketPairs()->then(function ($data) {
    foreach ($data as $exchange => $exchangeData) {
        print_r($exchangeData);
    }
})->otherwise(function ($error) {
    echo "Error: " . $error . PHP_EOL;
});


$aggregator->run();