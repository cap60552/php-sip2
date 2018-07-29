<?php

namespace lordelph\SIP2\Response;

/**
 * Class CheckoutResponse provides the response from a CheckoutRequest
 *
 * @method getOk()
 * @method getRenewalOk()
 * @method getMagnetic()
 * @method getDesensitize()
 * @method getTransactionDate()
 * @method getInstitutionId()
 * @method getPatronIdentifier()
 * @method getItemIdentifier()
 * @method getTitleIdentifier()
 * @method getDueDate()
 * @method getFeeType()
 * @method getSecurityInhibit()
 * @method getFeeAmount()
 * @method getCurrencyType()
 * @method getMediaType()
 * @method getItemProperties()
 * @method getTransactionId()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class CheckOutResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'Ok' => [],
        'RenewalOk' => [],
        'Magnetic' => [],
        'Desensitize' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AA_PATRON_IDENTIFIER,
        self::AB_ITEM_IDENTIFIER,
        self::AJ_TITLE_IDENTIFIER,
        self::AH_DUE_DATE,
        self::BT_FEE_TYPE,
        self::CI_SECURITY_INHIBIT,
        self::BH_CURRENCY_TYPE,
        self::BV_FEE_AMOUNT,
        self::CK_MEDIA_TYPE,
        self::CH_ITEM_PROPERTIES,
        self::BK_TRANSACTION_ID,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('Ok', substr($raw, 2, 1));
        $this->setVariable('RenewalOk', substr($raw, 3, 1));
        $this->setVariable('Magnetic', substr($raw, 4, 1));
        $this->setVariable('Desensitize', substr($raw, 5, 1));
        $this->setVariable('TransactionDate', substr($raw, 6, 18));

        $this->parseVariableData($raw, 24);
    }
}
