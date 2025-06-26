<?php

namespace Snowdog\CustomDescription\Api;

/**
 * Custom Description repository interface
 *
 * @api
 * @SuppressWarnings(PHPMD.ShortVariableName)
 */
interface CustomDescriptionRepositoryInterface
{
    /**
     * Retrieve a custom description by id
     *
     * @param int $id
     * @return \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * Retrieve list of all custom descriptions
     *
     * @return \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface[]
     */
    public function getAll();

    /**
     * Get list of custom descriptions by product ID
     *
     * @param int $productId
     * @return \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface[]
     */
    public function getListByProductId($productId);

    /**
     * Retrieve list of custom descriptions by product id
     *
     * @param int $productId
     * @return \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface[]
     */
    public function getCustomDescriptionByProductId($productId);

    /**
     * Save a custom description
     *
     * @param \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $customDescription
     * @return \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $customDescription);

    /**
     * Delete a custom description
     *
     * @param \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $customDescription
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $customDescription);
}
