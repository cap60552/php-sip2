<?php

namespace lordelph\SIP2\Response;

/**
 * Class PatronInformationResponse provides the response from a PatronInformationRequest
 *
 * @method getPatronStatus()
 * @method getLanguage()
 * @method getTransactionDate()
 * @method getHoldCount()
 * @method getOverdueCount()
 * @method getChargedCount()
 * @method getFineCount()
 * @method getRecallCount()
 * @method getUnavailableCount()
 * @method getInstitutionId()
 * @method getPatronIdentifier()
 * @method getPersonalName()
 * @method getHoldItemsLimit()
 * @method getOverdueItemsLimit()
 * @method getChargedItemsLimit()
 * @method getValidPatron()
 * @method getValidPatronPassword()
 * @method getFeeAmount()
 * @method getCurrencyType()
 * @method getFeeLimit()
 * @method getHoldItems()
 * @method getOverdueItems()
 * @method getFineItems()
 * @method getRecallItems()
 * @method getUnavailableHoldItems()
 * @method getHomeAddress()
 * @method getEmailAddress()
 * @method getHomePhoneNumber()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class PatronInformationResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'PatronStatus' => [],
        'Language' => [],
        'TransactionDate' => [],
        'HoldCount' => [],
        'OverdueCount' => [],
        'ChargedCount' => [],
        'FineCount' => [],
        'RecallCount' => [],
        'UnavailableCount' => [],
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AA_PATRON_IDENTIFIER,
        self::AE_PERSONAL_NAME,
        self::BZ_HOLD_ITEMS_LIMIT,
        self::CA_OVERDUE_ITEMS_LIMIT,
        self::CB_CHARGED_ITEMS_LIMIT,
        self::BL_VALID_PATRON,
        self::CQ_VALID_PATRON_PASSWORD,
        self::BH_CURRENCY_TYPE,
        self::BV_FEE_AMOUNT,
        self::CC_FEE_LIMIT,
        self::AS_HOLD_ITEMS,
        self::AT_OVERDUE_ITEMS,
        self::AU_CHARGED_ITEMS,
        self::AV_FINE_ITEMS,
        self::BU_RECALL_ITEMS,
        self::CD_UNAVAILABLE_HOLD_ITEMS,
        self::BD_HOME_ADDRESS,
        self::BE_EMAIL_ADDRESS,
        self::BF_HOME_PHONE_NUMBER,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('PatronStatus', substr($raw, 2, 14));
        $this->setVariable('Language', substr($raw, 16, 3));
        $this->setVariable('TransactionDate', substr($raw, 19, 18));
        $this->setVariable('HoldCount', substr($raw, 37, 4));
        $this->setVariable('OverdueCount', substr($raw, 41, 4));
        $this->setVariable('ChargedCount', substr($raw, 45, 4));
        $this->setVariable('FineCount', substr($raw, 49, 4));
        $this->setVariable('RecallCount', substr($raw, 53, 4));
        $this->setVariable('UnavailableCount', substr($raw, 57, 4));

        $this->parseVariableData($raw, 61);
    }
}
