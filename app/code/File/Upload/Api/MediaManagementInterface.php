<?php
namespace File\Upload\Api;

/**
 * Interface MediaManagementInterface
 * @api
 */
interface MediaManagementInterface
{
    /**
     * Upload media files
     *
     * @param int $customerId
     * @param int $addressId
     * @return string
     */
    public function upload($customerId, $addressId);
}
