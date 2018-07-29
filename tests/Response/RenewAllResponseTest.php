<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\RenewAllResponse;
use lordelph\SIP2\Response\RenewResponse;
use lordelph\SIP2\Response\SIP2Response;

class RenewAllResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw = $this->makeResponse("66" .
            "1" .
            "0002".
            "0003".
            "20180711    185645" .
            "AOinstitution|" .
            "BMbook 1|".
            "BMbook 2|".
            "BNbook 3|".
            "BNbook 4|".
            "BNbook 5|".
            "AFmessage|" .
            "AGprint|");

        /** @var RenewAllResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(RenewAllResponse::class, $response);

        $this->assertEquals('1', $response->getOk());
        $this->assertEquals('0002', $response->getRenewed());
        $this->assertEquals('0003', $response->getUnrenewed());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());

        $this->assertEquals('institution', $response->getInstitutionId());


        $this->assertCount(2, $response->getRenewedItems());
        $this->assertContains('book 1', $response->getRenewedItems());
        $this->assertContains('book 2', $response->getRenewedItems());

        $this->assertCount(3, $response->getUnrenewedItems());
        $this->assertContains('book 3', $response->getUnrenewedItems());
        $this->assertContains('book 4', $response->getUnrenewedItems());
        $this->assertContains('book 5', $response->getUnrenewedItems());
    }
}
