<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\EndSessionResponse;
use lordelph\SIP2\Response\SIP2Response;

class EndSessionResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =  $this->makeResponse("36".
            "Y".
            "20180711    185645".
            "AO1234|".
            "AApatron|".
            "AEJoe|".
            "AFmessage|".
            "AGprint|");

        /** @var EndSessionResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(EndSessionResponse::class, $response);

        $this->assertEquals('Y', $response->getEndSession());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals('1234', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertContains('message', $response->getScreenMessage());
        $this->assertContains('print', $response->getPrintLine());

        //we've thown an AE Personal name in the test response. We wouldn't expect this, but we
        //tolerate extra data...
        $this->assertTrue($response->hasVariable('PersonalName'));
        $this->assertEquals('Joe', $response->getVariable('PersonalName'));
    }
}
