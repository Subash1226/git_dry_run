<?php
namespace Customer\Address\Api;

interface AddressUpdaterInterface
{
    /**
     * Update customer address
     *
     * @param int $addressId
     * @param string $telephone
     * @param string $firstname
     * @param string $lastname
     * @param string $countryId
     * @param string $postcode
     * @param string $city
     * @param int $regionId
     * @param string $regionCode
     * @param string $region
     * @param string[] $street Array of street lines
     * @param boolean $isDefaultBilling
     * @param boolean $isDefaultShipping
     * @return string
     */
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
        array $street,
        $isDefaultBilling,
        $isDefaultShipping
    );

    /**
     * Create customer address
     * 
     * @param int $customerId
     * @param string $telephone
     * @param string $firstname
     * @param string $lastname
     * @param string $countryId
     * @param string $postcode
     * @param string $city
     * @param int $regionId
     * @param string $regionCode
     * @param string $region
     * @param string[] $street Array of street lines
     * @param boolean $isDefaultBilling
     * @param boolean $isDefaultShipping
     * @return string
     */
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
        array $street,
        $isDefaultBilling,
        $isDefaultShipping
    );
}
