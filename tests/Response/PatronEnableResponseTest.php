<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\PatronEnableResponse;
use lordelph\SIP2\Response\SIP2Response;

class PatronEnableResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =  $this->makeResponse("26" .
            "XXXXyyyXXXXyyy" .
            "ENG".
            "20180711    185645" .
            "AO1234|" .
            "AApatron|" .
            "AEJoe Tester|" .
            "BLY|".
            "CQY|".
            "AFmessage|" .
            "AGprint|");

        /** @var PatronEnableResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(PatronEnableResponse::class, $response);
        $this->assertEquals('XXXXyyyXXXXyyy', $response->getPatronStatus());
        $this->assertEquals('ENG', $response->getLanguage());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());

        $this->assertEquals('1234', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertEquals('Joe Tester', $response->getPersonalName());

        $this->assertEquals('Y', $response->getValidPatron());
        $this->assertEquals('Y', $response->getValidPatronPassword());
        $this->assertEquals('0', $response->getSequenceNumber());
    }
}
