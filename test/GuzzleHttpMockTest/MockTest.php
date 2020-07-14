<?php

namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Aeris\GuzzleHttpMock\Expect;
use Aeris\GuzzleHttpMock\Exception\UnexpectedHttpRequestException;

class MockTest extends BaseTest
{
    /** @test */
    public function shouldReturnAResponseForARequestObject()
    {
        $mockResponse =
            new Response(200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'hello' => 'world',
                    'howareyou' => 'today'
                ])
            );

        $request = new Request(
            'PUT',
            'http://example.com/foo?faz=baz',
            [
                'Content-Type' => 'application/json'
            ],
            json_encode(['shakeyo' => 'body'])
        );

        $this->httpMock
            ->shouldReceiveRequest($request)
            ->andRespondWith($mockResponse);

        $actualResponse = $this->guzzleClient
            ->put('http://example.com/foo', [
                'query' => ['faz' => 'baz'],
                'body' => json_encode(['shakeyo' => 'body']),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        $this->httpMock->verify();
        $this->assertSame($mockResponse, $actualResponse);
    }

    /** @test */
    public function shouldReturnAResponseForARequestWithConfiguration()
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
            ->withBodyParams([
                'shakeyo' => 'body'
            ])
            ->andRespondWith($mockResponse);


        $actualResponse = $this->guzzleClient
            ->put('http://example.com/foo', [
                'query' => ['faz' => 'baz'],
                'form_params' => ['shakeyo' => 'body'],
            ]);

        $this->httpMock->verify();
        $this->assertSame($mockResponse, $actualResponse);
    }

    /** @test */
    public function shouldRespondToMultipleRequestsWithTheSameResponse()
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
            ->once()
            ->withUrl('http://example.com/foo')
            ->withMethod('PUT')
            ->withQueryParams([
                'faz' => 'baz'
            ])
            ->withJsonBodyParams([
                'shakeyo' => 'body'
            ])
            ->andRespondWith($mockResponse);

        $this->httpMock
            ->shouldReceiveRequest()
            ->once()
            ->withUrl('http://example.com/foo')
            ->withMethod('PUT')
            ->withQueryParams([
                'faz' => 'baz'
            ])
            ->withJsonBodyParams([
                'shakeyo' => 'hands in the air like you just don\'t care'
            ])
            ->andRespondWith($mockResponse);


        $actualResponse = $this->guzzleClient
            ->put('http://example.com/foo', [
                'query' => ['faz' => 'baz'],
                'json' => ['shakeyo' => 'body'],
                'headers' => ['Content-Type' => 'application/json']
            ]);

        $actualResponse2 = $this->guzzleClient
            ->put('http://example.com/foo', [
                'query' => ['faz' => 'baz'],
                'json' => ['shakeyo' => 'hands in the air like you just don\'t care'],
                'headers' => ['Content-Type' => 'application/json']
            ]);


        $this->httpMock->verify();
        $this->assertSame($mockResponse, $actualResponse);
        $this->assertSame($mockResponse, $actualResponse2);
    }

    /** @test */
    public function shouldRespondWithSpecifiedResponseCode()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withUrl('http://example.com/foo')
            ->withMethod('GET')
            ->andRespondWithCode(234);

        $response = $this->guzzleClient
            ->get('http://example.com/foo');

        $this->httpMock->verify();
        $this->assertEquals(234, $response->getStatusCode());
    }

    /** @test */
    public function shouldRespondWithJson()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withUrl('http://example.com/foo')
            ->withMethod('GET')
            ->andRespondWithJson([
                'foo' => 'bar',
                'faz' => ['baz', 'shnaz'],
            ]);

        $response = $this->guzzleClient
            ->get('http://example.com/foo');

        $this->httpMock->verify();
        $this->assertEquals([
            'foo' => 'bar',
            'faz' => ['baz', 'shnaz'],
        ], json_decode((string)$response->getBody(), true));
    }

    /** @test */
    public function shouldRespondWithJsonAndStatusCode()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withUrl('http://example.com/foo')
            ->withMethod('GET')
            ->andRespondWithJson([
                'foo' => 'bar',
                'faz' => ['baz', 'shnaz'],
            ], $statusCode = 234);

        $response = $this->guzzleClient
            ->get('http://example.com/foo');

        $this->httpMock->verify();
        $this->assertEquals([
            'foo' => 'bar',
            'faz' => ['baz', 'shnaz'],
        ], json_decode((string)$response->getBody(), true));
        $this->assertEquals(234, $response->getStatusCode());
    }

    /** @test */
    public function shouldCheckMultipleExpectations()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/aaa')
            ->andRespondWithJson(['foo' => 'bar']);

        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('POST')
            ->withUrl('http://www.example.com/bbb')
            ->andRespondWithJson(['shazaam' => 'kabloom']);

        $responseA = $this->guzzleClient
            ->get('http://www.example.com/aaa');

        $responseB = $this->guzzleClient
            ->post('http://www.example.com/bbb');

        $this->httpMock->verify();

        $this->assertEquals(['foo' => 'bar'], json_decode((string)$responseA->getBody(), true));
        $this->assertEquals(['shazaam' => 'kabloom'], json_decode((string)$responseB->getBody(), true));
    }

    /** @test */
    public function shouldUseTheNextAvailableExpectationIfTheFirstIsUsedUp()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->times(2)
            ->withMethod('GET')
            ->withUrl('http://www.example.com/users')
            ->andRespondWithJson(['foo' => 'bar']);

        $this->httpMock
            ->shouldReceiveRequest()
            ->once()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/users')
            ->andRespondWithJson(['shazaam' => 'bologna']);

        // should use first expectation (x1)
        $responseA = $this->guzzleClient
            ->get('http://www.example.com/users');

        // should use first expectation (x2)
        $responseB = $this->guzzleClient
            ->get('http://www.example.com/users');

        // should use second expecation
        $responseC = $this->guzzleClient
            ->get('http://www.example.com/users');

        $this->assertEquals(['foo' => 'bar'], json_decode((string)$responseA->getBody(), true));
        $this->assertEquals(['foo' => 'bar'], json_decode((string)$responseB->getBody(), true));
        $this->assertEquals(['shazaam' => 'bologna'], json_decode((string)$responseC->getBody(), true));


        $this->httpMock->verify();
    }

    /** @test */
    public function shouldRespondWithDifferentMockResponseOnMultipleRequests()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->andRespondWithJson(['bar' => 'foo']);

        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/bar')
            ->andRespondWithJson(['foo' => 'bar']);

        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('POST')
            ->withUrl('http://www.example.com/missing')
            ->andRespondWith(new Response(200, [], 'interesting message'));

        $response1 = $this->guzzleClient
            ->get('http://www.example.com/foo');

        $response2 = $this->guzzleClient
            ->get('http://www.example.com/bar');

        $response3 = $this->guzzleClient->post('http://www.example.com/missing');

        $this->assertEquals(['bar' => 'foo'], json_decode((string)$response1->getBody(), true));
        $this->assertEquals(['foo' => 'bar'], json_decode((string)$response2->getBody(), true));
        $this->assertEquals('interesting message', (string)$response3->getBody());

        $this->httpMock->verify();
    }

    /** @test */
    public function shouldFailIfNoRequestIsConfigured()
    {
        $this->guzzleClient
            ->get('http://www.example.com/shazlooey', [
                'form_params' => ['not' => 'what I expected']
            ]);

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->httpMock->verify();
    }


    /** @test */
    public function shouldResponseUsingClosure()
    {
        $concatBodyAndFooHeader = function (RequestInterface $requestBody) {
            $bodyContents = (string)$requestBody->getBody();
            $fooHeader = $requestBody->getHeader('foo')[0];
            return new Response(200, [], "$bodyContents $fooHeader");
        };

        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.sundata.nl/')
            ->withBody(new Expect\Any())
            ->andRespondUsing($concatBodyAndFooHeader);

        $rsp = $this->guzzleClient
            ->get('http://www.sundata.nl/', [
                RequestOptions::BODY => "Marco",
                RequestOptions::HEADERS => ['foo' => 'Polo']
            ]);
        $this->assertEquals('Marco Polo', $rsp->getBody()->getContents());
    }

}
