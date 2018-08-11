<?php

namespace cap60552\SIP2;

use cap60552\SIP2\Response\LoginResponse;
use cap60552\SIP2\Response\SIP2Response;

class LoginResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =  $this->makeResponse("941");

        /** @var LoginResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertEquals('1', $response->getOk());
    }
}
