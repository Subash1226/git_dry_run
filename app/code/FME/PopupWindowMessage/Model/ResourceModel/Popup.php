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
namespace FME\PopupWindowMessage\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\EntityManager\EntityManager;

class Popup extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fme_pwm_master', 'pwm_id');
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        
        $table=$this->getTable('fme_pwm_store');
        $where = ['pwm_id = ?' => (int)$object->getPwmId()];
        $this->getConnection()->delete($table, $where);

        $insertdata = $object->getStoreId();

        if ($insertdata) {
            $dataCMS = [];
            foreach ($insertdata as $storeId) {
                $dataCMS[] = ['pwm_id' => (int)$object->getPwmId(), 'store_id' => (string)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $dataCMS);
        }

    }



    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {

        $select = $this->getConnection()->select()
        ->from($this->getTable('fme_pwm_store'))
        ->where('pwm_id = ?', $object->getId());

        if ($data = $this->getConnection()->fetchAll($select)) {
            $storesArray = [];
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            
            $object->setData('store_id', $storesArray);
        }

        //Get Category Ids
        // $category_ids = $object->getData('popup_categories');

        // if ($category_ids != "") {
        //     $object->setData('popup_categories', $category_ids);
        // }

        $cms_ids = $object->getData('cmspage_ids');

        if ($cms_ids != "") {
            $cmsPageIds = explode(",", $cms_ids);
            $result = array_unique($cmsPageIds);
            $object->setData('cmspage_id', $result);
        }

        $customer_group_ids = $object->getData('customer_group_ids');

        if ($customer_group_ids != "") {
            $cusGrpIds = explode(",", $customer_group_ids);
            $result = array_unique($cusGrpIds);
            $object->setData('customer_group_ids', $result);
        }

        $category_ids = $object->getData("popup_categories");
        if ($category_ids != "") {
            $object->setData("photogallery_categories", $category_ids);
        }
        //print_r($object->getData());exit;
        return parent::_afterLoad($object);
    }
}
