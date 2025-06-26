<?php

namespace Quick\Order\Block;

/**
 * Customform content block
 */
class Customform extends \Magento\Framework\View\Element\Template
{
    /**
     * Index constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        //$this->pageConfig->getTitle()->set(__(''));

        return parent::_prepareLayout();
    }
}
