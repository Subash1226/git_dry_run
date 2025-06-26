<?php

namespace Mobile\Avaliable\Api;

interface MobilenumberCheckInterface
{
    /**
     * Check if the mobile number is available.
     *
     * @api
     * @param string $mobilenumber
     * @return bool
     */
    public function checkMobilenumber($mobilenumber);
}
