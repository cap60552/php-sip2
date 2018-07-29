<?php

namespace lordelph\SIP2\Response;

/**
 * Class ItemInformationResponse provides the response from a ItemInformationRequest
 *
 * @method getCirculationStatus()
 * @method getSecurityMarker()
 * @method getFeeType()
 * @method getTransactionDate()
 * @method getHoldQueueLength()
 * @method getDueDate()
 * @method getRecallDate()
 * @method getHoldPickupDate()
 * @method getItemIdentifier()
 * @method getTitleIdentifier()
 * @method getOwner()
 * @method getCurrencyType()
 * @method getFeeAmount()
 * @method getMediaType()
 * @method getPermanentLocation()
 * @method getCurrentLocation()
 * @method getItemProperties()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class ItemInformationResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'CirculationStatus' => [],
        'SecurityMarker' => [],
        'FeeType' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::CF_HOLD_QUEUE_LENGTH,
        self::AH_DUE_DATE,
        self::CJ_RECALL_DATE,
        self::CM_HOLD_PICKUP_DATE,
        self::AB_ITEM_IDENTIFIER,
        self::AJ_TITLE_IDENTIFIER,
        self::BG_OWNER,
        self::BH_CURRENCY_TYPE,
        self::BV_FEE_AMOUNT,
        self::CK_MEDIA_TYPE,
        self::AQ_PERMANENT_LOCATION,
        self::AP_CURRENT_LOCATION,
        self::CH_ITEM_PROPERTIES,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('CirculationStatus', substr($raw, 2, 2));
        $this->setVariable('SecurityMarker', substr($raw, 4, 2));
        $this->setVariable('FeeType', substr($raw, 6, 2));
        $this->setVariable('TransactionDate', substr($raw, 8, 18));

        $this->parseVariableData($raw, 26);
    }
}
