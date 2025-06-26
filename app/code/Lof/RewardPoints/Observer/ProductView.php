<?php
namespace Lof\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductView implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\Registry            $coreRegistry
     * @param \Lof\RewardPoints\Helper\Balance\Earn  $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Model\Config         $rewardsConfig
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->rewardsBalanceEarn = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsConfig = $rewardsConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            $product = $this->coreRegistry->registry('product');
            if ($product) {
                $earningPoint = (float)$this->rewardsBalanceEarn->getProductPoints($product);
                $product->setEarningPoints($earningPoint);
                $spendingPoints = (float)$this->rewardsBalanceSpend->getProductSpendingPoints($product->getId(), true);
                if ($spendingPoints) {
                    $product->setSpendingPoints($spendingPoints)->setIsProductView(true);
                    $product->setEarningPoints(0);
                } else if ($earningPoint) {
                    $product->setEarningPoints($earningPoint);
                }
                $this->coreRegistry->unregister('product');
                $this->coreRegistry->register('product', $product);
            }
        }
    }
}
