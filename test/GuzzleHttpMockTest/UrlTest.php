<?php


namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;


use Aeris\GuzzleHttpMock\Exception\UnexpectedHttpRequestException;

class UrlTest extends BaseTest
{
    /** @test */
    public function url_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/shazlooey');

        $this->httpMock->verify();
    }

    /** @test */
    public function url_customLogic_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl(function ($url) {
                return preg_match('/foo$/', $url) === 1;
            });

        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function url_customLogic_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl(function ($url) {
                return preg_match('/foo$/', $url) === 1;
            });


        $this->guzzleClient
            ->get('http://www.example.com/shablooey');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->httpMock->verify();
    }
}