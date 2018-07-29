<?php

namespace lordelph\SIP2\Response;

/**
 * Class EndSessionResponse provides the response from a EndSessionRequest
 *
 * @method getEndSession()
 * @method getTransactionDate()
 * @method getInstitutionId()
 * @method getPatronIdentifier()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class EndSessionResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'EndSession' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AA_PATRON_IDENTIFIER,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('EndSession', substr($raw, 2, 1));
        $this->setVariable('TransactionDate', substr($raw, 3, 18));
        $this->parseVariableData($raw, 21);
    }
}
