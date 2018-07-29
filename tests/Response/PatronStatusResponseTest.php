<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\PatronStatusResponse;
use lordelph\SIP2\Response\SIP2Response;

class PatronStatusResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw = $this->makeResponse("24".
            "xxxYYYYxxxYYYY".
            "ENG".
            "20180711    185645".
            "AO1234|".
            "AApatron|".
            "AEJoe|".
            "BLY|".
            "CQY|".
            "BHGBP|".
            "BV1.23|".
            "AFmessage|".
            "AGprint|");

        /** @var PatronStatusResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(PatronStatusResponse::class, $response);
        $this->assertEquals('xxxYYYYxxxYYYY', $response->getPatronStatus());
        $this->assertEquals('ENG', $response->getLanguage());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());

        $this->assertEquals('1234', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertEquals('Joe', $response->getPersonalName());

        $this->assertEquals('Y', $response->getValidPatron());
        $this->assertEquals('Y', $response->getValidPatronPassword());
        $this->assertEquals('1.23', $response->getFeeAmount());
        $this->assertEquals('GBP', $response->getCurrencyType());
        $this->assertEquals('0', $response->getSequenceNumber());
    }
}
