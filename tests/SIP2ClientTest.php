<?php

namespace cap60552\SIP2;

use cap60552\SIP2\Exception\RuntimeException;
use cap60552\SIP2\Request\LoginRequest;
use cap60552\SIP2\Response\LoginResponse;
use Prophecy\Argument;

/**
 * SIP2ClientTest tests the connection capabilities of the client by mocking a socket pretending to be a SIP2 server
 */
class SIP2ClientTest extends AbstractSIP2ClientTest
{
    /**
     * Basic test of typical client/server interaction
     */
    public function testLogin()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a login response
        $responses = [
            $this->makeResponse("941")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect('10.0.0.0');

        $loginRequest = new LoginRequest();
        $loginRequest->setSIPLogin('username');
        $loginRequest->setSIPPassword('password');

        /** @var LoginResponse $response */
        $response = $client->sendRequest($loginRequest);
        $this->assertInstanceOf(LoginResponse::class, $response);

        $this->assertEquals('1', $response->getOk());

        $client->disconnect();
    }

    /**
     * Test that client will retry a request with bad CRC
     */
    public function testCRCFailureRetry()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a login response with a bad CRC, followed by a good one
        $responses = [
            "940AY0AZ1234\r",
            $this->makeResponse("941")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect('10.0.0.0');

        $loginRequest = new LoginRequest();
        $loginRequest->setSIPLogin('username');
        $loginRequest->setSIPPassword('password');

        /** @var LoginResponse $response */
        $response = $client->sendRequest($loginRequest);
        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertEquals('1', $response->getOk());
    }

    /**
     * Test that repeated failure of a SIP2 server to provide a valid CRC produces an exception
     *
     * @expectedException RuntimeException
     */
    public function testCRCFailureAbort()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a continued failure to provide a valid response, leading us to abort after
        //3 retries
        $responses = [
            "940AY0AZ1234\r",
            "940AY0AZ1234\r",
            "940AY0AZ1234\r",
            "940AY0AZ1234\r",
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect('10.0.0.0');

        $loginRequest = new LoginRequest();
        $loginRequest->setSIPLogin('username');
        $loginRequest->setSIPPassword('password');

        /** @var LoginResponse $response */
        $client->sendRequest($loginRequest); //exception should be thrown
    }

    /**
     * THis just verifies that Socket::bind is called if we've asked for a specific binding
     */
    public function testBinding()
    {
        $client = new SIP2Client;
        $client->setSocketFactory($this->createBindingTestMockSIP2Server());

        $client->connect('10.0.0.0', '192.168.1.1');

        //we don't really need an assertion, as its enough to reach here without exception
        //and the mock includes a prediction for a call on bind...
    }

    /**
     * Test that failure to connect throws exception
     *
     * @expectedException RuntimeException
     */
    public function testConnectionFailure()
    {
        $client = new SIP2Client;
        $client->setSocketFactory($this->createUnconnectableMockSIP2Server());
        $client->connect('10.0.0.0');
    }

    /**
     * This provides a socket factory which will always fail to connect
     * @return \Socket\Raw\Factory
     */
    protected function createUnconnectableMockSIP2Server()
    {
        $socket = $this->prophesize(\Socket\Raw\Socket::class);
        $socket->connect(Argument::type('string'))->will(function () {
            throw new \Exception('Test connection failure');
        });

        $socket->close()->willReturn(true);

        //our factory will always fail to connect...
        $factory = $this->prophesize(\Socket\Raw\Factory::class);
        $factory->createClient(
            Argument::type('string'),
            Argument::any()
        )->willReturn($socket->reveal());

        return $factory->reveal();
    }

    /**
     * This provides a socket factory which will verify the bind method is  called
     * @return \Socket\Raw\Factory
     */
    private function createBindingTestMockSIP2Server()
    {
        $socket = $this->prophesize(\Socket\Raw\Socket::class);
        $socket->connect(Argument::type('string'))->willReturn(true);

        //we verify bind gets called...
        $socket->bind(Argument::type('string'))->shouldBeCalled()->willReturn(true);

        //our factory will always fail to connect...
        $factory = $this->prophesize(\Socket\Raw\Factory::class);
        $factory->createClient(
            Argument::type('string'),
            Argument::any()
        )->willReturn($socket->reveal());

        return $factory->reveal();
    }
}
