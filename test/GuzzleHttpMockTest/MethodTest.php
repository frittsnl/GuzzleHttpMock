<?php


namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;


use Aeris\GuzzleHttpMock\Exception\UnexpectedHttpRequestException;

class MethodTest extends BaseTest
{

    /** @test */
    public function method_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('POST')
            ->withUrl('http://www.example.com/foo');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->httpMock->verify();
    }

    /** @test */
    public function method_match_customLogic_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod(function ($method) {
                return strlen($method) === 3;
            })
            ->withUrl('http://www.example.com/foo');

        $this->guzzleClient
            ->put('http://www.example.com/foo');

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function method_notMatch_customLogic_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod(function ($method) {
                return strlen($method) === 3;
            })
            ->withUrl('http://www.example.com/foo');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->post('http://www.example.com/foo');

        $this->httpMock->verify();
    }

}