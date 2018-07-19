<?php

namespace lordelph\SIP2;

/**
 * SequencingTest verifies the AY field in requests increases and wraps around after 10 requests
 * @package lordelph\SIP2
 */
class SequencingTest extends AbstractSIP2ClientTest
{
    public function testSequencing()
    {
        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server([]));

        for ($s=0; $s<=11; $s++) {
            $msg = $client->msgLogin('uu', 'pp');
            //9300CNuu|COpp|AY1AZF9E9

            $lastSep=strrpos($msg, '|');
            $ay=substr($msg, $lastSep+1, 2);
            $seq=substr($msg, $lastSep+3, 1);

            $this->assertEquals('AY', $ay);
            $this->assertEquals($s % 10, intval($seq));
        }
    }
}
