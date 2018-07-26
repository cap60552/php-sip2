<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\PatronInformationRequest;

class PatronInformationRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new PatronInformationRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setInstitutionId('Banjo');
        $req->setType('overdue');
        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals(
            "6300120180723    094611 Y        AOBanjo|AApaul|AC|ADfoo|BP1|BQ5|AY0AZED6E\r\n",
            $msg
        );
    }
}
