<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Exception\LogicException;
use lordelph\SIP2\Request\LoginRequest;

class SIP2RequestTest extends AbstractSIP2ClientTest
{
    /**
     * verifies the AY field in requests increases and wraps around after 10 requests
     */
    public function testSequencing()
    {
        for ($s=0; $s<=11; $s++) {
            $login = new LoginRequest();
            $login->setSIPLogin('user');
            $login->setSIPPassword('pass');

            $msg = $login->getMessageString();
            //9300CNuu|COpp|AY1AZF9E9

            $lastSep=strrpos($msg, '|');
            $ay=substr($msg, $lastSep+1, 2);
            $seq=substr($msg, $lastSep+3, 1);

            $this->assertEquals('AY', $ay);
            $this->assertEquals($s % 10, intval($seq));
        }
    }

    /**
     * Test that getting a variable after setting a default works...
     */
    public function testDefault()
    {
        $login = new LoginRequest();
        $login->setDefault('SIPLogin', 'foo');
        $this->assertEquals('foo', $login->getVariable('SIPLogin'));
    }

    /**
     * Test that getting a variable before setting one with no default throws exception
     */
    public function testMissingSet()
    {
        $this->expectException(LogicException::class);

        $login = new LoginRequest();
        $login->getVariable('SIPLogin');
    }

    /**
     * Test that getting a variable before setting one with no default throws exception
     */
    public function testBadMethodCall()
    {
        $this->expectException(LogicException::class);

        $login = new LoginRequest();
        $login->garbageCall();
    }
}
