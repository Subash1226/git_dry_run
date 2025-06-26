<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_PopupWindowMessage
 * @author    Dara Baig  (support@fmeextensions.com)
 * @copyright Copyright (c) 2018 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */
namespace FME\PopupWindowMessage\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;

class Popup extends Template
{
    public $popupHelper;
    protected $_customerSession;
    protected $_customerGroupCollection;
    protected $scopeConfig;
    protected $collectionFactory;
    protected $objectManager;
    protected $request;
    protected $date;
    protected $_messageManager;
    protected $registry;
    protected $rule;
    protected $productStatus;
    protected $productVisibility;
    protected $context;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Group $customerGroupCollection,
        \FME\PopupWindowMessage\Model\ResourceModel\Popup\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \FME\PopupWindowMessage\Helper\Popup $popupHelper,
        \FME\PopupWindowMessage\Model\PopupFactory $rule,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_customerSession = $customerSession;
        $this->_customerGroupCollection = $customerGroupCollection;
        $this->popupHelper = $popupHelper;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->context = $context;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->registry = $registry;
        $this->rule = $rule;
        $this->_messageManager = $messageManager;

        parent::__construct($context);
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }
    
    public function getPopupCollection()
    {

        $filters = $this->getRequest()->getPostValue();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('is_active', 1);

        return $collection;
    }
    public function getPopupCollectionCart()
    {
        $filters = $this->getRequest()->getPostValue();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('is_active', 1)->setOrder('priority', 'DESC');

        if (count($collection)>0) {
            return $collection->getData()[0];
        }
        return [];
    }

    public function getPopupCollectionCategory()
    {
        $filters = $this->getRequest()->getPostValue();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('is_active', 1)->setOrder('priority', 'DESC');
        $category = $this->objectManager->get('Magento\Framework\Registry')->registry('current_category');
        $cat_id=$category->getId();
        foreach ($collection as $popups) {
            $catpage=$popups->getPopupCategories();
            if ($catpage!="") {
                $arr_cat=explode(",", $catpage);
                if (in_array($cat_id, $arr_cat)) {
                    return $popups->getData();
                }
            }
        }
        return [];
    }

    public function getPopupCollectionProduct()
    {
        $filters = $this->getRequest()->getPostValue();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('is_active', 1)->setOrder('priority', 'DESC');
        foreach ($collection as $item) {
            $rule = $this->rule->create()
                    ->load($item->getId());
            if ($rule->getConditions()->validate($this->getCurrentProduct())) {
                return $item->getData();
            }
        }
        return [];
    }
    public function getPopupCollectionCms()
    {

        $filters = $this->getRequest()->getPostValue();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('is_active', 1)->setOrder('priority', 'DESC');
        $cmsPage = $this->objectManager->get('\Magento\Cms\Model\Page');
        foreach ($collection as $popups) {
            $cmspage=$popups->getCmspageIds();
            if ($cmspage!="") {
                $arr_cms=explode(",", $cmspage);
                if (in_array($cmsPage->getId(), $arr_cms)) {
                    return $popups->getData();
                }
            }
        }
        return [];
    }
    // public function testobject()
    // {   echo "<pre>";
    //     print_r($this->getPopupCollectionProduct());exit;
    // }
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    // public function getCurrentCategory()
    // {
    //     return $this->registry->registry('current_category');
    // }


    public function getConditionsStatusForProducts($popupId)
    {

        $rule = $this->rule->create()->load($popupId);
        if ($rule->getConditions()->validate($this->getCurrentProduct())) {
                    return true;
        }
    }

    // public function getConditionsStatusForCategory($popupId)
    // {
    //     print_r($rule->getConditions()->validate($this->getCurrentCategory()));
    //     exit();
    //     $rule = $this->rule->create()->load($popupId);
    //     if($rule->getConditions()->validate($this->getCurrentProduct())){
    //         return true;

    //     }
    // }
    public function getSerializeFormData()
    {
        $filters = $this->getRequest()->getPostValue();
        return json_encode($filters);
    }

    public function getCustomerGroup()
    {
        $currentGroupId = $this->_customerSession->getCustomer()->getGroupId();
        $collection = $this->_customerGroupCollection->load($currentGroupId);
        $CustomerGroupName = $collection->getCustomerGroupCode();
        return $currentGroupId;
    }
}
