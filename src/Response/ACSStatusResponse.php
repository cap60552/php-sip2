<?php

namespace lordelph\SIP2\Response;

/**
 * Class ACSStatusResponse provides the response from an SCStatus request
 *
 * @method getOk()
 * @method getOnline()
 * @method getCheckin()
 * @method getCheckout()
 * @method getRenewal()
 * @method getPatronUpdate()
 * @method getOffline()
 * @method getTimeout()
 * @method getRetries()
 * @method getTransactionDate()
 * @method getProtocol()
 * @method getInstitutionId()
 * @method getLibraryName()
 * @method getSupportedMessages()
 * @method getTerminalLocation()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class ACSStatusResponse extends SIP2Response
{
    protected $var = [
        'Online' => [],
        'Checkin' => [],
        'Checkout' => [],
        'Renewal' => [],
        'PatronUpdate' => [],
        'Offline' => [],
        'Timeout' => [],
        'Retries' => [],
        'TransactionDate' => [],
        'Protocol' => [],
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AM_LIBRARY_NAME,
        self::BX_SUPPORTED_MESSAGES,
        self::AN_TERMINAL_LOCATION,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('Online', substr($raw, 2, 1));
        $this->setVariable('Checkin', substr($raw, 3, 1));
        $this->setVariable('Checkout', substr($raw, 4, 1));
        $this->setVariable('Renewal', substr($raw, 5, 1));
        $this->setVariable('PatronUpdate', substr($raw, 6, 1));
        $this->setVariable('Offline', substr($raw, 7, 1));
        $this->setVariable('Timeout', substr($raw, 8, 3));
        $this->setVariable('Retries', substr($raw, 11, 3));
        $this->setVariable('TransactionDate', substr($raw, 14, 18));
        $this->setVariable('Protocol', substr($raw, 32, 4));

        $this->parseVariableData($raw, 36);
    }
}
