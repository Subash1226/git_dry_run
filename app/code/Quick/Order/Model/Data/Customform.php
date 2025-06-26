<?php
declare(strict_types=1);

namespace Quick\Order\Model\Data;

use Quick\Order\Api\Data\CustomformInterface;

class Customform extends \Magento\Framework\Api\AbstractExtensibleObject implements CustomformInterface
{
    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Quick\Order\Api\Data\CustomformExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Quick\Order\Api\Data\CustomformExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Quick\Order\Api\Data\CustomformExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get last_name
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set last_name
     * @param string $orderId
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get first_name
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set first_name
     * @param string $customerId
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
    
    /**
     * Get email
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->_get(self::CUSTOMER_NAME);
    }

    /**
     * Set email
     * @param string $customerName
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * Get message
     * @return string|null
     */
    public function getMobileNumber()
    {
        return $this->_get(self::MOBILE_NUMBER);
    }

    /**
     * Set message
     * @param string $mobileNumber
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setMobileNumber($mobileNumber)
    {
        return $this->setData(self::MOBILE_NUMBER, $mobileNumber);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getAddressEntity()
    {
        return $this->_get(self::ADDRESS_ENTITY);
    }

    /**
     * Set status
     * @param string $addressEntity
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setAddressEntity($addressEntity)
    {
        return $this->setData(self::ADDRESS_ENTITY, $addressEntity);
    }
	
	/**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
	
    /**
     * Get image
     * @return string|null
     */
    public function getImage()
    {
        return $this->_get(self::IMAGE);
    }

    /**
     * Set image
     * @param string $image
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get phone
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set phone
     * @param string $updatedAt
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

