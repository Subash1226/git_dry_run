<?php

namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\Data\AddressInterface;

class UpdateAddress extends Action
{
    protected $resultJsonFactory;
    protected $addressRepository;
    protected $regionFactory;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddressRepositoryInterface $addressRepository,
        RegionInterfaceFactory $regionFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();

        try {
            if (!isset($postData['address_id'])) {
                throw new LocalizedException(__('Address ID is missing.'));
            }

            $addressId = (int)$postData['address_id'];
            /** @var AddressInterface $address */
            $address = $this->addressRepository->getById($addressId);

            if (!$address->getId()) {
                throw new LocalizedException(__('Address not found.'));
            }

            $this->updateAddressFields($address, $postData);
            $this->addressRepository->save($address);

            return $result->setData(['success' => true]);
        } catch (LocalizedException $e) {
            $this->logger->error('Error updating address: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->logger->critical('Critical error updating address: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while updating the address. Please try again later.')
            ]);
        }
    }

    private function updateAddressFields(AddressInterface $address, array $postData)
    {
        $address->setFirstname($postData['firstname'] ?? $address->getFirstname())
            ->setLastname($postData['lastname'] ?? $address->getLastname())
            ->setCompany($postData['company'] ?? $address->getCompany())
            ->setStreet($postData['street'] ?? $address->getStreet())
            ->setCity($postData['city'] ?? $address->getCity())
            ->setPostcode($postData['postcode'] ?? $address->getPostcode())
            ->setTelephone($postData['telephone'] ?? $address->getTelephone());

        if (isset($postData['country_id'])) {
            $address->setCountryId($postData['country_id']);
        }

        // Handle region
        if (isset($postData['region_id'])) {
            $address->setRegionId($postData['region_id']);
        }

        $this->addressRepository->save($address);
    }
}
