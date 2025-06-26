<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\CustomerData;

class Points implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Lof\RewardPoints\Helper\Customer $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Data     $rewardsData
     * @param \Magento\Customer\Model\Session   $customerSession
     */
    public function __construct(
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsData     = $rewardsData;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        // Check if customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            return [
                'totalpoints' => 0,
                'formatted_points' => '0'
            ];
        }

        try {
            $customer = $this->rewardsCustomer->setForceSave(true)->getCustomer();

            if ($customer && $customer->getId()) {
                $totalPoints = (float) $customer->getTotalPoints();
                return [
                    'totalpoints' => $totalPoints,
                    'formatted_points' => $this->rewardsData->formatPoints($totalPoints, true, false)
                ];
            }
        } catch (\Exception $e) {
            // Log the error but don't break the page
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)
                ->error('Error loading reward points: ' . $e->getMessage());
        }

        return [
            'totalpoints' => 0,
            'formatted_points' => '0'
        ];
    }
}
