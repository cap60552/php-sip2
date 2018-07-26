<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\ItemInformationResponse;
use lordelph\SIP2\Response\SIP2Response;

class ItemInformationResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw = $this->makeResponse("18" .
            "01" .
            "02" .
            "03" .
            "20180711    185645" .
            "CF3|" .
            "AH20180711    185645|" .
            "CJ20180711    185646|" .
            "CM20180711    185647|" .
            "AB1565921879|" .
            "AJPerl 5 desktop reference|" .
            "BGBR1|" .
            "BHGBP|" .
            "BV1.23|" .
            "CK001|" .
            "AQBR2|" .
            "APBR3|" .
            "CHprop|" .
            "AFmessage|" .
            "AGprint|");

        /** @var ItemInformationResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(ItemInformationResponse::class, $response);

        $this->assertEquals('01', $response->getCirculationStatus());
        $this->assertEquals('02', $response->getSecurityMarker());
        $this->assertEquals('03', $response->getFeeType());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals('3', $response->getHoldQueueLength());
        $this->assertEquals('20180711    185645', $response->getDueDate());
        $this->assertEquals('20180711    185646', $response->getRecallDate());
        $this->assertEquals('20180711    185647', $response->getHoldPickupDate());
        $this->assertEquals('1565921879', $response->getItemIdentifier());
        $this->assertEquals('Perl 5 desktop reference', $response->getTitleIdentifier());
        $this->assertEquals('BR1', $response->getOwner());
        $this->assertEquals('GBP', $response->getCurrencyType());
        $this->assertEquals('1.23', $response->getFeeAmount());
        $this->assertEquals('001', $response->getMediaType());
        $this->assertEquals('BR2', $response->getPermanentLocation());
        $this->assertEquals('BR3', $response->getCurrentLocation());
        $this->assertEquals('prop', $response->getItemProperties());
        $this->assertContains('message', $response->getScreenMessage());
        $this->assertContains('print', $response->getPrintLine());
    }
}
