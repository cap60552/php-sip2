<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\CheckInRequest;

class CheckInRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new CheckInRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setItemIdentifier('1234');
        $req->setInstitutionId('Banjo');
        $req->setItemLocation('paul');
        $req->setItemReturnDate(strtotime('2018-08-01 10:00:00'));

        $msg = $req->getMessageString();

        $this->assertEquals(
            "09N20180723    09461120180801    100000APpaul|AOBanjo|AB1234|AC|BIN|AY0AZED90\r\n",
            $msg
        );
    }
}
