<?php


namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;


use Aeris\GuzzleHttpMock\Exception\UnexpectedHttpRequestException;
use Aeris\GuzzleHttpMock\Expect\Any;
use Aeris\GuzzleHttpMock\Expect\ArrayContains;
use GuzzleHttp\Psr7\Response;

class BodyTest extends BaseTest
{
    /** @test */
    public function bodyParams_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(['foo' => 'bar']);

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['not' => 'what I expected']
            ]);

        $this->httpMock->verify();
    }

    /** @test */
    public function bodyParams_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(['foo' => 'bar']);

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['foo' => 'bar']
            ]);

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function body_customLogic_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(function ($bodyParams) {
                return $bodyParams['foo'] === 'bar';
            });

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['foo' => 'bar']
            ]);

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function body_customLogic_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(function ($bodyParams) {
                return $bodyParams['foo'] === 'bar';
            });

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['foo' => 'shablooey']
            ]);

        $this->httpMock->verify();
    }

    public function body_anyParams_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(new Any());

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['foo' => 'bar']
            ]);

        $this->httpMock->verify();
    }

    /** @test */
    public function body_arrayContains_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(new ArrayContains(['foo' => 'bar']));

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['foo' => 'bar', 'faz' => 'baz']
            ]);

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function body_arrayContains_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams(new ArrayContains(['foo' => 'bar']));

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => ['foo' => 'shablooey', 'faz' => 'baz']
            ]);

        $this->httpMock->verify();
    }

    /** @test */
    public function body_outOfOrder_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams([
                'faz' => 'baz',
                'foo' => 'bar',
            ]);

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'form_params' => [
                    'foo' => 'bar',
                    'faz' => 'baz',
                ]
            ]);

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function body_outOfOrder_nullValues_match_shouldPass()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withBodyParams([
                'faz' => 'baz',
                'foo' => 'bar',
                'nullA' => null,
                'nullB' => null,
            ]);

        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'json' => [
                    'faz' => 'baz',
                    'foo' => 'bar',
                    'nullB' => null,
                    'nullA' => null,
                ]
            ]);

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function jsonBody_notMatch_shouldFail()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->withJsonBodyParams(['foo' => 'bar']);

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo', [
                'json' => ['not' => 'what I expected']
            ]);

        $this->httpMock->verify();
    }

    /** @test */
    public function shouldReturnAResponseForAJsonBodyParamsExpectation()
    {
        $mockResponse = new Response(200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'hello' => 'world',
                'howareyou' => 'today'
            ])
        );

        $this->httpMock
            ->shouldReceiveRequest()
            ->withUrl('http://example.com/foo')
            ->withMethod('PUT')
            ->withQueryParams([
                'faz' => 'baz'
            ])
            ->withJsonBodyParams([
                'shakeyo' => 'body'
            ])
            ->andRespondWith($mockResponse);


        $actualResponse = $this->guzzleClient
            ->put('http://example.com/foo', [
                'query' => ['faz' => 'baz'],
                'body' => json_encode(['shakeyo' => 'body']),
                'headers' => ['Content-Type' => 'application/json']
            ]);

        $this->httpMock->verify();
        $this->assertSame($mockResponse, $actualResponse);
    }
}