<?php
namespace Dynamic\ConsultationFee\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'consultation_fee/general/enabled';
    const XML_PATH_FEE_AMOUNT = 'consultation_fee/general/fee_amount';

    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFeeAmount($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FEE_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}