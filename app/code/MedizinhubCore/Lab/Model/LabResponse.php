<?php
namespace MedizinhubCore\Lab\Model;

class LabResponse implements \MedizinhubCore\Lab\Api\Data\LabResponseInterface
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var int|null
     */
    private $errorCode;

    /**
     * @var \MedizinhubCore\Lab\Api\Data\LabCartInterface[]|null
     */
    private $data;

    /**
     * {@inheritdoc}
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorCode($code)
    {
        $this->errorCode = $code;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}