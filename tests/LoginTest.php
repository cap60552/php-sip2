<?php

namespace lordelph\SIP2;

class LoginTest extends AbstractSIP2ClientTest
{
    public function testLogin()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a login response
        $responses = [
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
}
