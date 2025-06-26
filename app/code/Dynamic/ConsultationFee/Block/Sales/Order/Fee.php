<?php
namespace Dynamic\ConsultationFee\Block\Sales\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

class Fee extends Template
{
    protected $order;

    public function __construct(Context $context, Order $order, array $data = [])
    {
        parent::__construct($context, $data);
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getConsultationFee()
    {
        return $this->getOrder()->getConsultationFee();
    }
}
