<?php


namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;


use Aeris\GuzzleHttpMock\Exception\UnexpectedHttpRequestException;

class QueryTest extends BaseTest
{
    /** @test */
    public function shouldCompareQueryStringLiterally()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withQueryString('param1=value1&param2=%20spaces')
            ->andRespondWithJson(['bar' => 'foo']);

        $response1 = $this->guzzleClient
            ->get('http://www.example.com/foo?param1=value1&param2=%20spaces');

        $this->assertEquals(['bar' => 'foo'], json_decode((string)$response1->getBody(), true));
    }


    /** @test */
    public function query_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withQueryParams(['foo' => 'bar']);

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/shazlooey', [
                'query' => ['not' => 'what I expected']
            ]);

        $this->httpMock->verify();
    }

    /** @test */
    public function queryParams_customLogic_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withQueryParams(function ($actualParams) {
                return $actualParams['foo'] === 'bar';
            });

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'query' => [
                    'foo' => 'bar',
                    'faz' => 'shazaam'
                ]
            ]);

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function queryParams_customLogic_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withQueryParams(function ($actualParams) {
                return $actualParams['foo'] === 'notTheActualValueOfFoo';
            });

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'query' => [
                    'foo' => 'bar',
                    'faz' => 'shazaam'
                ]
            ]);


        $this->httpMock->verify();
    }
}