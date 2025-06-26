<?php
namespace Mobile\Avaliable\Api\Response;

interface MobilenumberCheckResponseInterface
{
    /**
     * @return bool
     */
    public function isAvailable();

    /**
     * @param bool $isAvailable
     * @return void
    */
    public function setIsAvailable($isAvailable);
}
