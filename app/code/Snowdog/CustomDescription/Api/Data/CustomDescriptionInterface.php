<?php

namespace Snowdog\CustomDescription\Api\Data;

/**
 * Custom Description interface
 *
 * @api
 * @SuppressWarnings(PHPMD.ShortVariableName)
 */
interface CustomDescriptionInterface
{
    const PRODUCT_ID                 = 'product_id';
    const TITLE                      = 'title';
    const DESCRIPTION                = 'price';
    const IMAGE                      = 'image';
    const POSITION                   = 'position';
    const EXPIRY_DATE                = 'expiry_date';
    const QUANTITY                   = 'quantity';
    const PURCHASE_RATE              = 'purchase_rate';
    const PURCHASE_QUANTITY          = 'purchase_quantity';
    const FIELD_CUSTOM_STATUS        = 'expiry_status_option';
    const FIELD_COMMENTS             = 'comments';
    const EXPIRY_STATUS              = 'expiry_status';
    const SPECIAL_PRICE              = 'special_price';
    const SPECIAL_PRICE_FROM_DATE    = 'special_price_from_date';
    const SPECIAL_PRICE_TO_DATE      = 'special_price_to_date';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * @return string|null
     */
    public function getExpiryDate();

    /**
     * @param string|null $expiryDate
     * @return $this
     */
    public function setExpiryDate($expiryDate);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return float
     */
    public function getPurchaseRate();

    /**
     * @param float $purchaseRate
     * @return $this
     */
    public function setPurchaseRate($purchaseRate);

    /**
     * @return int
     */
    public function getPurchaseQuantity();

    /**
     * @param int $purchaseQuantity
     * @return $this
     */
    public function setPurchaseQuantity($purchaseQuantity);

    /**
     * @return string|null
     */
    public function getExpiryStatus();

    /**
     * @param string|null $expiryStatus
     * @return $this
     */
    public function setExpiryStatus($expiryStatus);

    /**
     * @return string|null
     */
    public function getSpecialPriceFromDate();

    /**
     * @param string|null $specialPriceFromDate
     * @return $this
     */
    public function setSpecialPriceFromDate($specialPriceFromDate);

    /**
     * @return string|null
     */
    public function getSpecialPriceToDate();

    /**
     * @param string|null $specialPriceToDate
     * @return $this
     */
    public function setSpecialPriceToDate($specialPriceToDate);

    /**
     * Get custom status.
     *
     * @return string|null
     */
    public function getCustomStatus();

    /**
     * Set custom status.
     *
     * @param string|null $customStatus
     * @return $this
     */
    public function setCustomStatus($customStatus);

    /**
     * Get comments.
     *
     * @return string|null
     */
    public function getComments();

    /**
     * Set comments.
     *
     * @param string|null $comments
     * @return $this
     */
    public function setComments($comments);

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
}
