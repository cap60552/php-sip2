<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\CheckOutResponse;
use lordelph\SIP2\Response\SIP2Response;

class CheckOutResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw = $this->makeResponse("12" .
            "1" .
            "Y" .
            "N" .
            "Y" .
            "20180711    185645" .
            "AO1234|" .
            "AApatron|" .
            "ABitem|" .
            "AJtitle|" .
            "AH20180711    185645|" .
            "BT01|" .
            "CIN|" .
            "BHGBP|" .
            "BV1.23|" .
            "CKmda|" .
            "CHprop|" .
            "BKxyz|" .
            "AFmessage|" .
            "AGprint|");

        /** @var CheckOutResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(CheckOutResponse::class, $response);

        $this->assertEquals('1', $response->getOk());
        $this->assertEquals('Y', $response->getRenewalOk());
        $this->assertEquals('N', $response->getMagnetic());
        $this->assertEquals('Y', $response->getDesensitize());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());

        $this->assertEquals('1234', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertEquals('item', $response->getItemIdentifier());
        $this->assertEquals('title', $response->getTitleIdentifier());
        $this->assertEquals('20180711    185645', $response->getDueDate());
        $this->assertEquals('01', $response->getFeeType());
        $this->assertEquals('N', $response->getSecurityInhibit());
        $this->assertEquals('GBP', $response->getCurrencyType());
        $this->assertEquals('1.23', $response->getFeeAmount());
        $this->assertEquals('mda', $response->getMediaType());
        $this->assertEquals('prop', $response->getItemProperties());
        $this->assertEquals('xyz', $response->getTransactionId());
    }
}
