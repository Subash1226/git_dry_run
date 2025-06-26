<?php
namespace Mhb\RestrictCod\Plugin;

use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use Mhb\RestrictCod\Helper\Data as RestrictCodHelper;

class CashOnDeliveryPlugin
{
    /**
     * @var RestrictCodHelper
     */
    protected $restrictCodHelper;

    /**
     * Constructor
     *
     * @param RestrictCodHelper $restrictCodHelper
     */
    public function __construct(
        RestrictCodHelper $restrictCodHelper
    ) {
        $this->restrictCodHelper = $restrictCodHelper;
    }

    /**
     * Check if COD is available for the current quote
     *
     * @param Cashondelivery $subject
     * @param bool $result
     * @param CartInterface|null $quote
     * @return bool
     */
    public function afterIsAvailable(
        Cashondelivery $subject,
        $result,
        CartInterface $quote = null
    ) {
        if (!$result || $quote === null) {
            return $result;
        }

        // If module is not enabled, use original result
        if (!$this->restrictCodHelper->isEnabled()) {
            return $result;
        }

        $shippingAddress = $quote->getShippingAddress();
        
        if (!$shippingAddress || !$shippingAddress->getRegionCode()) {
            // If no shipping address or region, use original result
            return $result;
        }

        $stateCode = $shippingAddress->getRegionCode();
        
        return $this->restrictCodHelper->isAllowedForState($stateCode);
    }
}