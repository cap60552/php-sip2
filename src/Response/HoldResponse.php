<?php

namespace lordelph\SIP2\Response;

/**
 * Class HoldResponse provides the response from a HoldRequest
 *
 * @method getOk()
 * @method getAvailable()
 * @method getTransactionDate()
 * @method getExpirationDate()
 * @method getQueuePosition()
 * @method getPickupLocation()
 * @method getInstitutionId()
 * @method getPatronIdentifier()
 * @method getItemIdentifier()
 * @method getTitleIdentifier()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class HoldResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'Ok' => [],
        'Available' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::BW_EXPIRATION_DATE,
        self::BR_QUEUE_POSITION,
        self::BS_PICKUP_LOCATION,
        self::AO_INSTITUTION_ID,
        self::AA_PATRON_IDENTIFIER,
        self::AB_ITEM_IDENTIFIER,
        self::AJ_TITLE_IDENTIFIER,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        /*
         *   $result = [];
        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
                'available' => substr($response, 3, 1),
                'TransactionDate' => substr($response, 4, 18)
            ];

        //expiration date is optional an indicated by BW
        $variableOffset = 22;
        if (substr($response, 22, 2) === 'BW') {
            $result['fixed']['ExpirationDate'] = substr($response, 24, 18);
            $variableOffset = 42;
        }

        $result['variable'] = $this->parseVariableData($response, $variableOffset);
         */
        $this->setVariable('Ok', substr($raw, 2, 1));
        $this->setVariable('Available', substr($raw, 3, 1));
        $this->setVariable('TransactionDate', substr($raw, 4, 18));
        $this->parseVariableData($raw, 22);
    }
}
