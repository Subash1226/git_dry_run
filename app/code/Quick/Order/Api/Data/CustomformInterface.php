<?php
declare(strict_types=1);

namespace Quick\Order\Api\Data;

interface CustomformInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
	const ID = 'id';
    // const MESSAGE = 'message';
    const ORDER_ID = 'order_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_NAME = 'customer_name';
    const MOBILE_NUMBER = 'mobile_number';
    const ADDRESS_ENTITY = 'address_entity';
	// const LAST_NAME = 'last_name';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    // const PHONE = 'phone';
    const IMAGE = 'image';
	const STATUS = 'status';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Quick\Order\Api\Data\CustomformExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Quick\Order\Api\Data\CustomformExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Quick\Order\Api\Data\CustomformExtensionInterface $extensionAttributes
    );
    /**
     * Get last_name
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set last_name
     * @param string $orderId
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setOrderId($orderId);

    /**
     * Get first_name
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set first_name
     * @param string $customerId
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get first_name
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set first_name
     * @param string $customerName
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get first_name
     * @return string|null
     */
    public function getMobileNumber();

    /**
     * Set first_name
     * @param string $mobileNumber
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setMobileNumber($mobileNumber);
	

    /**
     * Get image
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     * @param string $image
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setImage($image);
	
	/**
     * Get status
     * @return string|null
     */
    public function getAddressEntity();

    /**
     * Set status
     * @param string $addressEntity
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setAddressEntity($addressEntity);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setStatus($status);
	
    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setCreatedAt($createdAt);
    /**
     * Get created_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set created_at
     * @param string $updatedAt
     * @return \Quick\Order\Api\Data\CustomformInterface
     */
    public function setUpdatedAt($updatedAt);
}

