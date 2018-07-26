<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\CheckInResponse;
use lordelph\SIP2\Response\SIP2Response;

class CheckInResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =  $this->makeResponse("10" .
            "1" .
            "Y" .
            "U" .
            "N" .
            "20180711    185645" .
            "AO1234|" .
            "ABitem|" .
            "AQloc|" .
            "AJtitle|" .
            "CLsort|" .
            "AApatron|" .
            "CKmda|" .
            "CHprop|" .
            "AFmessage|" .
            "AGprint|");

        /** @var CheckInResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(CheckInResponse::class, $response);

        $this->assertEquals('1', $response->getOk());
        $this->assertEquals('Y', $response->getResensitize());
        $this->assertEquals('U', $response->getMagnetic());
        $this->assertEquals('N', $response->getAlert());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());

        $this->assertEquals('1234', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertEquals('sort', $response->getSortBin());
        $this->assertEquals('item', $response->getItemIdentifier());
        $this->assertEquals('title', $response->getTitleIdentifier());
        $this->assertEquals('loc', $response->getPermanentLocation());
        $this->assertEquals('mda', $response->getMediaType());
        $this->assertEquals('prop', $response->getItemProperties());
    }
}
