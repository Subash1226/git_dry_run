<?php

namespace MedizinhubCore\Whatsapp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'whatsapp_notifications/general/enabled';
    const XML_PATH_API_KEY = 'whatsapp_notifications/general/api_key';
    const XML_PATH_ORDER_TEMPLATE = 'whatsapp_notifications/templates/order_general_template';
    const XML_PATH_TRACKING_TEMPLATE = 'whatsapp_notifications/templates/tracking_template';

    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderTemplateName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getTrackingTemplateName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TRACKING_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}