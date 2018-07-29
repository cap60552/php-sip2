<?php

namespace lordelph\SIP2\Response;

/**
 * Class LoginResponse provides the response from a LoginRequest
 *
 * @method getOk()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class LoginResponse extends SIP2Response
{
    protected $var = [
        'Ok' => ['type' => 'n'],
    ];

    public function __construct($raw)
    {
        $this->setVariable('Ok', substr($raw, 2, 1));
    }
}
