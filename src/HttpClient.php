<?php

namespace App;

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Socket\Connector;

class HttpClient
{
    private $browser;

    public function __construct(LoopInterface $loop, array $config = [])
    {
        $connector = new Connector($loop, $config);
        $this->browser = new Browser($loop, $connector);
    }

    public function get(string $url, array $headers = []): PromiseInterface
    {
        return $this->browser->get($url, $headers)->then(function (ResponseInterface $response) {
            return json_decode((string)$response->getBody());
        });
    }

    public function postJson(string $url, $data, array $headers = []): PromiseInterface
    {
        $headers['Content-Type'] = 'application/json';
        $body = json_encode($data);
        return $this->browser->post($url, $headers, $body)->then(function (ResponseInterface $response) {
            return json_decode((string)$response->getBody());
        });
    }
}
