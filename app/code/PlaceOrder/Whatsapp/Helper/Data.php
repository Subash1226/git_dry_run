<?php
namespace PlaceOrder\Whatsapp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'whatsapp_settings/general/enabled';
    const XML_PATH_API_KEY = 'whatsapp_settings/general/api_key';
    const XML_PATH_NEW_ORDER_CAMPAIGN = 'whatsapp_settings/general/new_order_campaign_name';
    const XML_PATH_STATUS_CHANGE_CAMPAIGN = 'whatsapp_settings/general/status_change_campaign_name';
    const XML_PATH_STAFF_NUMBERS = 'whatsapp_settings/general/staff_numbers';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Check if WhatsApp notifications are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get API Key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Campaign Name for New Orders
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCampaignName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NEW_ORDER_CAMPAIGN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'new_order_staff_notification';
    }

    /**
     * Get Campaign Name for Status Changes
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStatusChangeCampaignName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STATUS_CHANGE_CAMPAIGN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'order_status_change_notification';
    }

    /**
     * Get Staff Phone Numbers
     *
     * @param int|null $storeId
     * @return array
     */
    public function getStaffNumbers($storeId = null)
    {
        $numbers = $this->scopeConfig->getValue(
            self::XML_PATH_STAFF_NUMBERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($numbers)) {
            return [];
        }

        // Split by comma and clean up
        $numbersArray = explode(',', $numbers);
        $cleanNumbers = [];
        
        foreach ($numbersArray as $number) {
            $cleanNumber = trim($number);
            if (!empty($cleanNumber)) {
                $cleanNumbers[] = $cleanNumber;
            }
        }

        return $cleanNumbers;
    }
}