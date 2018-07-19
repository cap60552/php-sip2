<?php

namespace lordelph\SIP2;

/**
 * CRCFailureTest checks the behaviour when the CRC in a response is incorrect - we check that we can retry
 * sending a request, and also that we give up after a number of attempts
 * @package lordelph\SIP2
 */
class CRCFailureTest extends AbstractSIP2ClientTest
{
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

        $client->connect();

        $msg = $client->msgLogin('username', 'password');
        $response = $client->getMessage($msg);

        $info = $client->parseLoginResponse($response);
        $this->assertFixedMetadata('1', $info, 'Ok');
    }

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

        $client->connect();

        $msg = $client->msgLogin('username', 'password');
        $response = $client->getMessage($msg);
        $this->assertFalse($response);
    }
}
