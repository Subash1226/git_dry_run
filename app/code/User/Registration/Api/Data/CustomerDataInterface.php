<?php
namespace User\Registration\Api\Data;

interface CustomerDataInterface
{
    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return void
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return void
     */
    public function setLastname($lastname);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId);

    /**
     * Get mobile number
     *
     * @return string
     */
    public function getMobileNumber();

    /**
     * Set mobile number
     *
     * @param string $mobileNumber
     * @return void
     */
    public function setMobileNumber($mobileNumber);
}
