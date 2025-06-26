<?php

namespace Quick\Order\Block;

use Magento\Framework\View\Element\Template;

class AddressList extends Template
{
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setTemplate('Quick_Order::addresslist.phtml');
    }
}
