<?php
namespace MedizinhubCore\Mrp\Model\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

class Fee extends AbstractTotal
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

        $oldPriceTotal = 0;
        foreach ($quote->getAllItems() as $item) {
            $oldPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $qty = $item->getQty();
            $oldPriceTotal += $oldPrice * $qty;
        }

        $total->setOldPriceTotal($oldPriceTotal);

        return $this;
    }

    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        // Recalculate Order based on the laMrp quote data
        $oldPriceTotal = 0;
        foreach ($quote->getAllItems() as $item) {
            $oldPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $qty = $item->getQty();
            $oldPriceTotal += $oldPrice * $qty;
        }

        return [
            'code' => 'order',
            'title' => __('Order Value'),
            'value' => $oldPriceTotal // Display the recalculated Order
        ];
    }

    public function getLabel()
    {
        return __('Order Value');
    }
}
