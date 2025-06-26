<?php
namespace MedizinhubCore\Lab\Api\Data;

interface LabResponseInterface
{
    /**
     * Get success status
     *
     * @return bool
     */
    public function getSuccess();

    /**
     * Set success status
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get error code
     *
     * @return int|null
     */
    public function getErrorCode();

    /**
     * Set error code
     *
     * @param int $code
     * @return $this
     */
    public function setErrorCode($code);

    /**
     * Get response data
     *
     * @return \MedizinhubCore\Lab\Api\Data\LabCartInterface[]|null
     */
    public function getData();

    /**
     * Set response data
     *
     * @param \MedizinhubCore\Lab\Api\Data\LabCartInterface[]|null $data
     * @return $this
     */
    public function setData($data);
}