<?php
namespace Customer\Address\Model;

use Customer\Address\Api\AddressUpdaterInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\ResponseFactory;

class AddressUpdater implements AddressUpdaterInterface
{
    protected $addressFactory;
    protected $customerFactory;
    protected $responseFactory;

    public function __construct(
        AddressFactory $addressFactory,
        CustomerFactory $customerFactory,
        ResponseFactory $responseFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->responseFactory = $responseFactory;
    }

    public function updateCustomerAddress(
        $addressId,
        $telephone,
        $firstname,
        $lastname,
        $countryId,
        $postcode,
        $city,
        $regionId,
        $regionCode,
        $region,
        $street,
        $isDefaultBilling,
        $isDefaultShipping
    ) {
        $address = $this->addressFactory->create()->load($addressId);
        if (!$address->getId()) {
            return json_encode([
                'success' => false,
                'message' => 'Address not found.'
            ]);
        }
    
        $address->setTelephone($telephone)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setCountryId($countryId)
                ->setPostcode($postcode)
                ->setCity($city)
                ->setRegionId($regionId)
                ->setRegionCode($regionCode)
                ->setRegion($region)
                ->setStreet($street)
                ->setIsDefaultBilling($isDefaultBilling)
                ->setIsDefaultShipping($isDefaultShipping)
                ->save();
    
        $customerId = $address->getCustomerId();
        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if (!$customer->getId()) {
                return json_encode([
                    'success' => false,
                    'message' => 'Customer not found.'
                ]);
            }
    
            if ($isDefaultBilling) {
                $customer->setDefaultBilling($addressId);
            } elseif ($customer->getDefaultBilling() == $address->getId()) {
                $customer->setDefaultBilling(Null);
            }
            if ($isDefaultShipping) {
                $customer->setDefaultShipping($addressId);
            } elseif ($customer->getDefaultShipping() == $address->getId()) {
                $customer->setDefaultShipping(Null);
            }
    
            $customer->save();
    
            return $address->getData();
        }
    
        return json_encode([
            'success' => false,
            'message' => 'An error occurred while updating the address.'
        ]);
    }
    

    public function createCustomerAddress(
        $customerId,
        $telephone,
        $firstname,
        $lastname,
        $countryId,
        $postcode,
        $city,
        $regionId,
        $regionCode,
        $region,
        $street,
        $isDefaultBilling,
        $isDefaultShipping
    ) {
        $address = $this->addressFactory->create();
        $address->setCustomerId($customerId)
                ->setTelephone($telephone)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setCountryId($countryId)
                ->setPostcode($postcode)
                ->setCity($city)
                ->setRegionId($regionId)
                ->setRegionCode($regionCode)
                ->setRegion($region)
                ->setStreet($street)
                ->save();
    
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer->getId()) {
            if ($isDefaultBilling) {
                $customer->setDefaultBilling($address->getId());
            }
            if ($isDefaultShipping) {
                $customer->setDefaultShipping($address->getId());
            }
            $customer->save();
    
            return $address->getData();
        }
    
        return json_encode([
            'success' => false,
            'message' => 'Customer not found.'
        ]);
    }
    
}
