<?php
namespace MedizinhubCore\Lab\Model\Data;

use MedizinhubCore\Lab\Api\Data\LabCartInterface;

class LabCart implements LabCartInterface
{
    private $entityId;
    private $customerId;
    private $status;
    private $testName;
    private $createdAt;

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getTestName()
    {
        return $this->testName;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setTestName($testName)
    {
        $this->testName = $testName;
        return $this;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}