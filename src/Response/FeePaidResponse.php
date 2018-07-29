<?php

namespace lordelph\SIP2\Response;

/**
 * Class FeePaidResponse provides the response from a FeePaidRequest
 *
 * @method getPaymentAccepted()
 * @method getTransactionDate()
 * @method getInstitutionId()
 * @method getPatronIdentifier()
 * @method getTransactionId()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class FeePaidResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'PaymentAccepted' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AA_PATRON_IDENTIFIER,
        self::BK_TRANSACTION_ID,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('PaymentAccepted', substr($raw, 2, 1));
        $this->setVariable('TransactionDate', substr($raw, 3, 18));
        $this->parseVariableData($raw, 21);
    }
}
