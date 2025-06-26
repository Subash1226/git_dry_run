<?php
namespace Magento\Checkout\Api\Data;

/**
 * Extension class for @see \Magento\Checkout\Api\Data\ShippingInformationInterface
 */
class ShippingInformationExtension extends \Magento\Framework\Api\AbstractSimpleObject implements ShippingInformationExtensionInterface
{
    /**
     * @return integer|null
     */
    public function getFee()
    {
        return $this->_get('fee');
    }

    /**
     * @param integer $fee
     * @return $this
     */
    public function setFee($fee)
    {
        $this->setData('fee', $fee);
        return $this;
    }
}
