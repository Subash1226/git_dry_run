<?php

namespace Quick\Order\Block;

use Magento\Framework\View\Element\Template\Context;
use Quick\Order\Model\CustomformFactory;
use Magento\Cms\Model\Template\FilterProvider;
/**
 * Customform View block
 */
class CustomformView extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Customform
     */
    protected $_customform;
    public function __construct(
        Context $context,
        CustomformFactory $customform,
        FilterProvider $filterProvider
    ) {
        $this->_customform = $customform;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Magelearn Customform Module View Page'));
        
        return parent::_prepareLayout();
    }

    public function getSingleData()
    {
        $id = $this->getRequest()->getParam('id');
        $customform = $this->_customform->create();
        $singleData = $customform->load($id);
        if($singleData->getId() || $singleData['id'] && $singleData->getStatus() == 1){
            return $singleData;
        }else{
            return false;
        }
    }
}