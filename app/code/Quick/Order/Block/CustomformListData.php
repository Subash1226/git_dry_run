<?php

namespace Quick\Order\Block;

use Magento\Framework\View\Element\Template\Context;
use Quick\Order\Model\CustomformFactory;
/**
 * Customform List block
 */
class CustomformListData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Customform
     */
    protected $_customform;
    public function __construct(
        Context $context,
        CustomformFactory $customform
    ) {
        $this->_customform = $customform;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Magelearn Customform Module List Page'));
        
        if ($this->getCustomformCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'quick.order.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getCustomformCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCustomformCollection()->load();
        }
        return parent::_prepareLayout();
    }

    public function getCustomformCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $customform = $this->_customform->create();
        $collection = $customform->getCollection();
        $collection->addFieldToFilter('status','1');
        //$customform->setOrder('id','ASC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}