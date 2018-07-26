<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\BlockPatronRequest;

class BlockPatronRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new BlockPatronRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setMessage('You are blocked');

        $msg = $req->getMessageString();

        $this->assertEquals("01N20180723    094611AOBanjo|ALYou are blocked|AApaul|AC|AY0AZED68\r\n", $msg);
    }
}
