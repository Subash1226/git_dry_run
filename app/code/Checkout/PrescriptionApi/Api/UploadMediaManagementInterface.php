<?php
namespace Checkout\PrescriptionApi\Api;

/**
 * Interface UploadMediaManagementInterface
 * @api
 */
interface UploadMediaManagementInterface
{
    /**
     * Upload media file
     *
     * @param int $orderId
     * @param int $cartId
     * @return string
     */
    public function upload($orderId, $cartId);
}
