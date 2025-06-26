<?php
namespace MedizinhubCore\Lab\Api;

interface LabCartManagementInterface
{
    /**
     * Create new lab cart
     *
     * @param \MedizinhubCore\Lab\Api\Data\LabCartInterface $labCart
     * @return \MedizinhubCore\Lab\Api\Data\LabResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(\MedizinhubCore\Lab\Api\Data\LabCartInterface $labCart);

    /**
     * Get list of lab carts for current customer
     *
     * @return \MedizinhubCore\Lab\Api\Data\LabResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();
    
    /**
     * Update lab cart
     *
     * @param \MedizinhubCore\Lab\Api\Data\LabCartInterface $labCart
     * @return \MedizinhubCore\Lab\Api\Data\LabResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update(\MedizinhubCore\Lab\Api\Data\LabCartInterface $labCart);

    /**
     * Delete lab cart
     *
     * @param string $entityId
     * @return \MedizinhubCore\Lab\Api\Data\LabResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete($entityId);
}