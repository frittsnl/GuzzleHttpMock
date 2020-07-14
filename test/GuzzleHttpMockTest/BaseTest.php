<?php


namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;


use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use Aeris\GuzzleHttpMock\Mock;

abstract class BaseTest extends TestCase
{
    /** @var GuzzleClient */
    protected $guzzleClient;

    /** @var Mock */
    protected $httpMock;

    protected function setUp(): void
    {
        $this->httpMock = new Mock();
        $handlerStack = $this->httpMock->getHandlerStackWithMiddleware();
        $this->guzzleClient = new GuzzleClient(['handler' => $handlerStack]);
    }
}