<?php

namespace lordelph\SIP2\Response;

/**
 * Class PatronStatusResponse provides the response from a PatronStatusRequest and a BlockPatronRequest
 *
 * @method getPatronStatus()
 * @method getLanguage()
 * @method getTransactionDate()
 * @method getInstitutionId()
 * @method getPatronIdentifier()
 * @method getPersonalName()
 * @method getValidPatron()
 * @method getValidPatronPassword()
 * @method getFeeAmount()
 * @method getCurrencyType()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class PatronStatusResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'PatronStatus' => [],
        'Language' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AA_PATRON_IDENTIFIER,
        self::AE_PERSONAL_NAME,
        self::BL_VALID_PATRON,
        self::CQ_VALID_PATRON_PASSWORD,
        self::BH_CURRENCY_TYPE,
        self::BV_FEE_AMOUNT,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('PatronStatus', substr($raw, 2, 14));
        $this->setVariable('Language', substr($raw, 16, 3));
        $this->setVariable('TransactionDate', substr($raw, 19, 18));

        $this->parseVariableData($raw, 37);
    }
}
