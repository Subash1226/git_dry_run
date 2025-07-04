<?php
namespace Lof\RewardPoints\Observer;

use Lof\RewardPoints\Model\Config as RewarsConfig;
use Lof\RewardPoints\Model\Purchase as RewadsPurchase;

class SalesQuoteRemoveItem implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Lof\RewardPoints\Helper\Data     $rewardsData
     * @param \Lof\RewardPoints\Helper\Purchase $rewardsPurchase
     * @param \Lof\RewardPoints\Model\Config    $rewardsConfig
     */
    public function __construct(
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        $this->rewardsData     = $rewardsData;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsConfig   = $rewardsConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            $purchase = $this->rewardsPurchase->getPurchase();
            $item     = $observer->getQuoteItem();

            $purchaseParams  = $purchase->getParams();
            if ($item && $item->getItemId()) {
                $quote           = $this->rewardsData->getQuote();
                $itemsCollection = $quote->getItemsCollection();
                if (isset($purchaseParams[RewarsConfig::SPENDING_PRODUCT_POINTS]['items'])) {
                    $items = $purchaseParams[RewarsConfig::SPENDING_PRODUCT_POINTS]['items'];
                    foreach ($items as $sku => $_item) {
                        /**
                         * Get Item id if empty
                         */
                        if (!$_item['item_id']) {
                            foreach ($itemsCollection as $cartItem) {
                                if (strtolower($cartItem->getSku()) == $sku) {
                                    $_item['item_id'] = $cartItem->getItemId();
                                }
                            }
                        }
                        if (isset($_item['item_id']) && $_item['item_id'] == $item->getId()) {
                            unset($items[$sku]);
                        }
                    }
                    $purchaseParams[RewarsConfig::SPENDING_PRODUCT_POINTS]['items'] = $items;
                    $purchase->setParams($purchaseParams);
                    $purchase->refreshPoints();
                }
            }

            /**
             * Update Purchase Points
             */
            $purchase->save();
        }
    }
}
