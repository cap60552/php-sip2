<?php

namespace lordelph\SIP2\Request;

/**
 * LoginRequest can be used to login to an ACS server program. The ACS should respond with the Login Response
 * message. Whether to use this message or to use some other mechanism to login to the ACS is configurable on the
 * SC. When this message is used, it will be the first message sent to the ACS.
 *
 * You must call setSIPLogin and setSIPPassword
 *
 * @method setUserIdAlgorithm(string $algorithm)
 * @method setPasswordAlgorithm(string $algorithm)
 * @method setSIPLogin(string $username)
 * @method setSIPPassword(string $password)
 * @method setLocation(string $location)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class LoginRequest extends SIP2Request
{
    protected $var = [
        'UserIdAlgorithm' => ['type' => 'n', 'default' => '0'],
        'PasswordAlgorithm' => ['type' => 'n', 'default' => '0'],
        'SIPLogin' => [],
        'SIPPassword' => [],
        'Location' => ['default' => '']
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('93');
        $this->addFixedOption($this->getVariable('UserIdAlgorithm'), 1);
        $this->addFixedOption($this->getVariable('PasswordAlgorithm'), 1);
        $this->addVarOption('CN', $this->getVariable('SIPLogin'));
        $this->addVarOption('CO', $this->getVariable('SIPPassword'));
        $this->addVarOption('CP', $this->getVariable('Location'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
