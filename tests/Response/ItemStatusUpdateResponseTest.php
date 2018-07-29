<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\ItemStatusUpdateResponse;
use lordelph\SIP2\Response\SIP2Response;

class ItemStatusUpdateResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =$this->makeResponse("20" .
            "1" .
            "20180711    185645" .
            "AB1565921879|" .
            "AJPerl 5 desktop reference|" .
            "CHprop|" .
            "AFmessage|" .
            "AGprint|");

        /** @var ItemStatusUpdateResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(ItemStatusUpdateResponse::class, $response);

        $this->assertEquals('1', $response->getPropertiesOk());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals('1565921879', $response->getItemIdentifier());
        $this->assertEquals('Perl 5 desktop reference', $response->getTitleIdentifier());
        $this->assertEquals('prop', $response->getItemProperties());
        $this->assertContains('message', $response->getScreenMessage());
        $this->assertContains('print', $response->getPrintLine());
    }
}
