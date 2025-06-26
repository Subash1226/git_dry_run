<?php

namespace Lof\RewardPoints\Model\Quote;

use Lof\RewardPoints\Model\Config;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Rule
     */
    protected $purchase;

    /**
     * Constructor
     *
     * @param \Lof\RewardPoints\Helper\Data $rewardsData
     * @param \Lof\RewardPoints\Helper\Purchase $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Customer $rewardsCustomer
     * @param \Lof\RewardPoints\Logger\Logger $rewardsLogger
     */
    public function __construct(
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        $this->setCode('rewardsdiscount');
        $this->rewardsData = $rewardsData;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsLogger = $rewardsLogger;
    }

    public function getPurchase()
    {
        return $this->purchase;
    }

    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($quote->getId()) {
            $result = [];
            $purchase = $this->getPurchase();
            if (!$purchase) {
                $purchase = $this->rewardsPurchase->getPurchase($quote);
                $this->setPurchase($purchase);
                $this->rewardsCustomer->refreshPurchaseAvailable($purchase->getId(), $quote->getCustomer()->getId());
            }
            $spentPoints = $purchase->getSpendPoints();
            $discount = $purchase->getDiscount();
            if ($spentPoints && $discount) {
                $result = [
                    'code' => $this->getCode(),
                    'title' => __('Use %1', $this->rewardsData->formatPoints($spentPoints)),
                    'value' => -$discount,
                    'is_formated' => true,
                    'strong' => true,
                ];
            }
            return $result;
        }
    }

    public function getLabel()
    {
        return __('Rewards Discount');
    }
}
