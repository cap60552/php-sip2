<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\FeePaidResponse;
use lordelph\SIP2\Response\SIP2Response;

class FeePaidResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =  $this->makeResponse("38".
            "Y".
            "20180711    185645".
            "AO1234|".
            "AApatron|".
            "BK5555|".
            "AFmessage|".
            "AGprint|");

        /** @var FeePaidResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(FeePaidResponse::class, $response);

        $this->assertEquals('Y', $response->getPaymentAccepted());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals('1234', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertEquals('5555', $response->getTransactionId());
        $this->assertContains('message', $response->getScreenMessage());
        $this->assertContains('print', $response->getPrintLine());
    }
}
