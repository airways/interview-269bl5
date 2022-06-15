<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class ControllerTestCase extends PHPUnit_TestCase
{
    protected function getAppInstance(): App
    {
        return require __DIR__ . '/../bootstrap.php';
    }

    protected function createRequest(
        string $method,
        string $path,
        string $query = '',
        array $headers = ['HTTP_ACCEPT' => 'text/html'],
        array $cookies = [],
        array $serverParams = []
    ): SlimRequest {
        $uri = new Uri('', '', 8080, $path, $query);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}
