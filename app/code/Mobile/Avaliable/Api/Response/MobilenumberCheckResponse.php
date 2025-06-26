<?php
namespace Mobile\Avaliable\Api\Response;

use Mobile\Avaliable\Api\Response\MobilenumberCheckResponseInterface;

class MobilenumberCheckResponse implements MobilenumberCheckResponseInterface
{
    protected $isAvailable;

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return $this->isAvailable;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAvailable($isAvailable)
    {
        $this->isAvailable = $isAvailable;
    }
}
