<?php
namespace Magento\Checkout\Api\Data;

/**
 * ExtensionInterface class for @see \Magento\Checkout\Api\Data\ShippingInformationInterface
 */
interface ShippingInformationExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface
{
    /**
     * @return integer|null
     */
    public function getFee();

    /**
     * @param integer $fee
     * @return $this
     */
    public function setFee($fee);
}
