<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\HoldResponse;
use lordelph\SIP2\Response\SIP2Response;

class HoldResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw = $this->makeResponse("161Y20180711    185645BW20180711    185645|" .
            "BR1|BSLibrary|AO123|AApatron|ABitem|AJtitle|AFthankyou|AGprint|");

        /** @var HoldResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(HoldResponse::class, $response);

        $this->assertEquals('1', $response->getOk());
        $this->assertEquals('Y', $response->getAvailable());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals('20180711    185645', $response->getExpirationDate());

        $this->assertEquals('1', $response->getQueuePosition());
        $this->assertEquals('Library', $response->getPickupLocation());
        $this->assertEquals('123', $response->getInstitutionId());
        $this->assertEquals('patron', $response->getPatronIdentifier());
        $this->assertEquals('item', $response->getItemIdentifier());
        $this->assertEquals('title', $response->getTitleIdentifier());
        $this->assertContains('thankyou', $response->getScreenMessage());
        $this->assertContains('print', $response->getPrintLine());
    }
}
