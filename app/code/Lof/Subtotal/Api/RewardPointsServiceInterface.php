<?php
namespace Lof\Subtotal\Api;

interface RewardPointsServiceInterface
{
    /**
     * Generate reward points for an order
     *
     * @param int $orderId
     * @return array
     */
    public function generatePoints($orderId);
}
