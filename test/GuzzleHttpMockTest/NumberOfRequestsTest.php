<?php


namespace Aeris\GuzzleHttpMock\Test\GuzzleHttpMockTest;


use Aeris\GuzzleHttpMock\Exception\CompoundUnexpectedHttpRequestException;
use Aeris\GuzzleHttpMock\Exception\UnexpectedHttpRequestException;
use Exception;

class NumberOfRequestsTest extends BaseTest
{

    /** @test */
    public function shouldPassIfTheRequestIsMadeTheNumberOfSetTimes()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->times(3);

        $this->guzzleClient
            ->get('http://www.example.com/foo');
        $this->guzzleClient
            ->get('http://www.example.com/foo');
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->assertTrue($this->httpMock->verify());
    }

    /**
     * @testWith [0,1,2,5]
     * @param int $nrOfRequests
     * @throws CompoundUnexpectedHttpRequestException
     */
    public function shouldPassRegardlessOfTheNumberOfRequests(int $nrOfRequests)
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->zeroOrMoreTimes();

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach (range(0, $nrOfRequests) as $i) {
            $this->guzzleClient
                ->get('http://www.example.com/foo');
        }

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function shouldPassIfTheRequestIsMadeOnce()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo');

        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function shouldPassIfTheRequestIsMadeAsManyTimesAsExpected()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->times(3);

        $this->guzzleClient
            ->get('http://www.example.com/foo');
        $this->guzzleClient
            ->get('http://www.example.com/foo');
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->assertTrue($this->httpMock->verify());
    }

    /** @test */
    public function shouldFailIfTheRequestIsMadeLessThanTheSetTimes()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->times(3);

        $this->guzzleClient
            ->get('http://www.example.com/foo');
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->httpMock->verify();
    }

    /** @test */
    public function shouldFailIfTheRequestIsMadeMoreThanTheSetTimes()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo')
            ->times(2);

        $this->guzzleClient
            ->get('http://www.example.com/foo');
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->httpMock->verify();
    }

    /** @test */
    public function shouldFailIfTheRequestIsMadeMoreThanOnce()
    {
        $this->httpMock
            ->shouldReceiveRequest()
            ->withMethod('GET')
            ->withUrl('http://www.example.com/foo');

        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->expectException(UnexpectedHttpRequestException::class);
        $this->guzzleClient
            ->get('http://www.example.com/foo');

        $this->httpMock->verify();
    }

    /** @test */
    public function shouldFailIfTheRequestIsNotMade()
    {
        $this->httpMock
            ->shouldReceiveRequest();

        $this->expectException(UnexpectedHttpRequestException::class);
        try {
            throw new Exception('too bad.');
        } catch (Exception $ex) {
            $this->httpMock->verify();
            throw $ex;
        }
    }


}