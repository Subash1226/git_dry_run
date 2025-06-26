<?php

namespace Quick\Order\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\AddressFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;

class UpdateAddress extends Action
{
    protected $resultJsonFactory;
    protected $addressFactory;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddressFactory $addressFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressFactory = $addressFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();

        try {
            if (
                isset($postData['address_id']) &&
                isset($postData['customer_name']) &&
                isset($postData['last_name']) &&
                isset($postData['company']) &&
                isset($postData['houseNo']) &&
                isset($postData['street']) &&
                isset($postData['city']) &&
                isset($postData['region']) &&
                isset($postData['region_id']) &&
                isset($postData['postcode']) &&
                isset($postData['telephone'])
            ) {
                $addressId = $postData['address_id'];
                $customerName = $postData['customer_name'];
                $lastName = $postData['last_name'];
                $company = $postData['company'];
                $street = [
                    $postData['houseNo'],
                    $postData['street']
                ];
                $city = $postData['city'];
                $region = $postData['region'];
                $regionId = $postData['region_id'];
                $postcode = $postData['postcode'];
                $telephone = $postData['telephone'];

                $addressModel = $this->addressFactory->create()->load($addressId);
                if ($addressModel->getId()) {
                    $addressModel->setFirstname($customerName)
                        ->setLastname($lastName)
                        ->setCompany($company)
                        ->setStreet($street)
                        ->setCity($city)
                        ->setRegion($region)
                        ->setRegionId($regionId)
                        ->setPostcode($postcode)
                        ->setTelephone($telephone)
                        ->save();

                    $this->logger->info('Received data:', $postData);

                    $result->setData(['success' => true]);
                } else {
                    $result->setData(['success' => false, 'message' => 'Address not found']);
                }

            } else {
                $result->setData(['success' => false, 'message' => 'Missing required fields']);
            }
        } catch (LocalizedException $e) {
            $this->logger->error('Error updating address:', ['exception' => $e]);
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->critical('Critical error updating address:', ['exception' => $e]);
            $result->setData([
                'success' => false,
                'message' => __('An error occurred while updating the address. Please try again later.')
            ]);
        }

        return $result;
    }
}
