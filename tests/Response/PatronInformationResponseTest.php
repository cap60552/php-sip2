<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\PatronInformationResponse;
use lordelph\SIP2\Response\SIP2Response;

class PatronInformationResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =   $this->makeResponse("64              00020180711    185645000000000010000000000009" .
            "AOExample City Library|AA1381380|AEMr Joe Tester|BZ9999|CA8888|CB7777|BLY|CQY|BV0.00|" .
            "AS123|AS456|".
            "BEjoe.tester@example.com|");

        /** @var PatronInformationResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(PatronInformationResponse::class, $response);
        $this->assertEquals('              ', $response->getPatronStatus());
        $this->assertEquals('000', $response->getLanguage());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals(0, $response->getHoldCount());
        $this->assertEquals(10, $response->getChargedCount());
        $this->assertEquals(0, $response->getFineCount());
        $this->assertEquals(0, $response->getRecallCount());
        $this->assertEquals(9, $response->getUnavailableCount());

        $this->assertEquals('Example City Library', $response->getInstitutionId());
        $this->assertEquals('1381380', $response->getPatronIdentifier());
        $this->assertEquals('Mr Joe Tester', $response->getPersonalName());
        $this->assertEquals('9999', $response->getHoldItemsLimit());
        $this->assertEquals('8888', $response->getOverdueItemsLimit());
        $this->assertEquals('7777', $response->getChargedItemsLimit());
        $this->assertEquals('Y', $response->getValidPatron());
        $this->assertEquals('Y', $response->getValidPatronPassword());
        $this->assertEquals('0.00', $response->getFeeAmount());
        $this->assertEquals('joe.tester@example.com', $response->getEmailAddress());
        $this->assertEquals('0', $response->getSequenceNumber());

        $this->assertContains('123', $response->getHoldItems());
        $this->assertContains('456', $response->getHoldItems());
    }
}
