<?php
namespace Sparsh\MobileNumberLogin\Block\Form;

use Sparsh\MobileNumberLogin\Setup\Patch\Data\AddCustomerAttributes;

/**
 * PHP version 8.2
 * 
 * Class UpdateMobileNumber
 * @package Sparsh\MobileNumberLogin\Block\Form
 */
class UpdateMobileNumber extends \Magento\Customer\Block\Form\Edit
{
    /**
     * Get customer custom mobile number attribute as value.
     *
     * @return string Customer phone number value.
     */
    public function getMobileNumber()
    {
        $mobileNumberAttribute = $this->getCustomer()
            ->getCustomAttribute(AddCustomerAttributes::MOBILE_NUMBER);
        return $mobileNumberAttribute ? (string) $mobileNumberAttribute->getValue() : null;
    }

    /**
     * Get customer custom country code attribute as value.
     *
     * @return string Customer phone number value.
     */
    public function getCountryCode()
    {
        $countryCodeAttribute = $this->getCustomer()
            ->getCustomAttribute(AddCustomerAttributes::COUNTRY_CODE);
        return $countryCodeAttribute ? (string) $countryCodeAttribute->getValue() : null;
    }
}
