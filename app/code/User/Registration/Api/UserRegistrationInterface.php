<?php
namespace User\Registration\Api;

use User\Registration\Api\Data\CustomerDataInterface;

interface UserRegistrationInterface
{
    /**
     * Create a new customer and register mobile number
     *
     * @param CustomerDataInterface $customer
     * @param string $password
     * @return string
     */
    public function createCustomer(CustomerDataInterface $customer, $password);
}
