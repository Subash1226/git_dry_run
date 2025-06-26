<?php

namespace Snowdog\CustomDescription\Model;

use Magento\Framework\Model\AbstractModel;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;

/**
 * Class CustomDescription
 * @package Snowdog\CustomDescription\Model
 */
class CustomDescription extends AbstractModel implements CustomDescriptionInterface
{
    const PRODUCT_ID = 'product_id';
    const TITLE = 'title';
    const DESCRIPTION = 'price';
    const IMAGE = 'image';
    const POSITION = 'position';
    const EXPIRY_DATE = 'expiry_date';
    const EXPIRY_STATUS = 'expiry_status';
    const PRICE = 'special_price';
    const SPECIAL_PRICE_FROM_DATE = 'special_price_from_date';
    const SPECIAL_PRICE_TO_DATE = 'special_price_to_date';
    const PURCHASE_RATE = 'purchase_rate';
    const QUANTITY = 'quantity';
    const PURCHASE_QUANTITY = 'purchase_quantity';
    const FIELD_COMMENTS = 'comments';
    const FIELD_CUSTOM_STATUS = 'expiry_status_option';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\Snowdog\CustomDescription\Model\Resource\CustomDescription::class);
    }

    /**
     * Get custom description list from a given product id
     *
     * @param int $productId
     * @return array
     */
    public function getCustomDescriptionByProductId($productId)
    {
        return $this->getResource()->getCustomDescriptionByProductId($productId);
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritdoc
     */
    public function getExpiryDate()
    {
        return $this->getData(self::EXPIRY_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setExpiryDate($expiryDate)
    {
        return $this->setData(self::EXPIRY_DATE, $expiryDate);
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getPurchaseRate()
    {
        return $this->getData(self::PURCHASE_RATE);
    }

    /**
     * @inheritdoc
     */
    public function setPurchaseRate($purchaseRate)
    {
        return $this->setData(self::PURCHASE_RATE, $purchaseRate);
    }

    /**
     * @inheritdoc
     */
    public function getPurchaseQuantity()
    {
        return $this->getData(self::PURCHASE_QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setPurchaseQuantity($purchaseQuantity)
    {
        return $this->setData(self::PURCHASE_QUANTITY, $purchaseQuantity);
    }



    /**
     * @inheritdoc
     */
    public function getQty()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setQty($qty)
    {
        return $this->setData(self::QUANTITY, $qty);
    }

    /**
     * @inheritdoc
     */
    public function getExpiryStatus()
    {
        return $this->getData(self::EXPIRY_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setExpiryStatus($expiryStatus)
    {
        return $this->setData(self::EXPIRY_STATUS, $expiryStatus);
    }

    /**
     * @inheritdoc
     */
    public function getSpecialPriceFromDate()
    {
        return $this->getData(self::SPECIAL_PRICE_FROM_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setSpecialPriceFromDate($specialPriceFromDate)
    {
        return $this->setData(self::SPECIAL_PRICE_FROM_DATE, $specialPriceFromDate);
    }

    /**
     * @inheritdoc
     */
    public function getSpecialPriceToDate()
    {
        return $this->getData(self::SPECIAL_PRICE_TO_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setSpecialPriceToDate($specialPriceToDate)
    {
        return $this->setData(self::SPECIAL_PRICE_TO_DATE, $specialPriceToDate);
    }

    /**
     * @inheritdoc
     */
    public function setComments($comments)
    {
        return $this->setData(self::FIELD_COMMENTS, $comments);
    }

    /**
     * @inheritdoc
     */
    public function getComments()
    {
        return $this->getData(self::FIELD_COMMENTS);
    }

    /**
     * @inheritdoc
     */
    public function setCustomStatus($customStatus)
    {
        return $this->setData(self::FIELD_CUSTOM_STATUS, $customStatus);
    }

    /**
     * @inheritdoc
     */
    public function getCustomStatus()
    {
        return $this->getData(self::FIELD_CUSTOM_STATUS);
    }
}
