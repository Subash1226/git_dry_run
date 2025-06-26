<?php
namespace MedizinhubCore\Summary\Model\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

class Savings extends AbstractTotal
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $savings = 0;
        foreach ($quote->getAllItems() as $item) {
            $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $finalPrice = $item->getProduct()->getFinalPrice();
            $qty = $item->getQty();
            $savings += ($regularPrice - $finalPrice) * $qty;
        }

        $total->setSavings($savings);

        return $this;
    }

    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $savings = 0;
        foreach ($quote->getAllItems() as $item) {
            $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $finalPrice = $item->getProduct()->getFinalPrice();
            $qty = $item->getQty();
            $savings += ($regularPrice - $finalPrice) * $qty;
        }

        return [
            'code' => 'savings',
            'title' => __('Discount'),
            'value' => $savings
        ];
    }

    public function getLabel()
    {
        return __('Discount');
    }
}
