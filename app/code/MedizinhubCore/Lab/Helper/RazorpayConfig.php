<?php
namespace MedizinhubCore\Lab\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class RazorpayConfig extends AbstractHelper
{
    /**
     * Configuration paths
     */
    const XML_PATH_RAZORPAY_ENABLED = 'payment/razorpay/active';
    const XML_PATH_RAZORPAY_KEY_ID = 'payment/razorpay/key_id';
    const XML_PATH_RAZORPAY_KEY_SECRET = 'payment/razorpay/key_secret';

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Check if Razorpay payment method is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RAZORPAY_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Razorpay Key ID
     *
     * @param int|null $storeId
     * @return string
     */
    public function getKeyId($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RAZORPAY_KEY_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Razorpay Key Secret
     *
     * @param int|null $storeId
     * @return string
     */
    public function getKeySecret($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RAZORPAY_KEY_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Validate Razorpay configuration
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isConfigValid($storeId = null)
    {
        return $this->isEnabled($storeId)
            && !empty($this->getKeyId($storeId))
            && !empty($this->getKeySecret($storeId));
    }
}
