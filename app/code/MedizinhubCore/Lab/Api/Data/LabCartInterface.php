<?php
namespace MedizinhubCore\Lab\Api\Data;

interface LabCartInterface
{
    /**
     * @return int|null
     */
    public function getEntityId();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getTestName();

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @param string $testName
     * @return $this
     */
    public function setTestName($testName);

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}