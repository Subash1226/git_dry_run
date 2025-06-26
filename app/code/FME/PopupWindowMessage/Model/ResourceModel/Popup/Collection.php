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
namespace FME\PopupWindowMessage\Model\ResourceModel\Popup;

use \FME\PopupWindowMessage\Model\ResourceModel\AbstractCollection;
use Magento\Framework\App\ObjectManager;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'pwm_id';
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('FME\PopupWindowMessage\Model\Popup', 'FME\PopupWindowMessage\Model\ResourceModel\Popup');
        $this->_map['fields']['pwm_id'] = 'main_table.pwm_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }


    // public function addStoreFilter($store, $withAdmin = true)
    // {
    //     $this->getSelect()
    //             ->join(
    //                 ['store_table' => $this->getTable('fme_product_label_store')],
    //                 'main_table.product_label_id = store_table.product_label_id',
    //                 []
    //             )
    //             ->where('store_table.store_id in (?)', [0, $store])
    //             ->distinct(true);
    //     return $this;
    // }
    // public function addCustomerGroupFilter($value)
    // {
    //     $this->getSelect()
    //             ->join(
    //                 ['cg' => $this->getTable('fme_product_label_customer_group')],
    //                 'main_table.product_label_id = cg.product_label_id',
    //                 []
    //             )
    //             ->where('cg.customer_group_id = ?', new \Zend_Db_Expr($value));
    //     return $this;
    // }

    //fme_pwm_master
    public function addCmsFilter($cmsid)
    {
        $this->getSelect()
                ->where('main_table.cmspage_ids in ('.$cmsid.')');

        return $this;
    }
    // public function addStatusFilter($isActive = true)
    // {
    //     $this->getSelect()
    //             ->where('main_table.status = ? ', $isActive);

    //     return $this;
    // }


    protected function _afterLoad()
    {
        $this->performAfterLoad('fme_pwm_store', 'pwm_id');
        $this->_previewFlag = false;
        return parent::_afterLoad();
    }
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr($this->serialize(['attribute' => $attributeCode]), 5, -1));
        $this->addFieldToFilter('conditions_serialized', ['like' => $match]);

        return $this;
    }
    public function addIdFilter($ruleId, $exclude = false)
    {
        if (is_array($ruleId)) {
            if (!empty($ruleId)) {
                if ($exclude) {
                    $condition = ['nin' => $ruleId];
                } else {
                    $condition = ['in' => $ruleId];
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = ['neq' => $ruleId];
            } else {
                $condition = $ruleId;
            }
        }

        $this->addFieldToFilter('percentage_pricing_id', $ruleId);
        return $this;
    }

    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('fme_pwm_store', 'pwm_id');
    }
    
    private function serialize($data)
    {
        if (class_exists(\Magento\Framework\Serialize\SerializerInterface::class)) {
            $serializer = ObjectManager::getInstance()->create(\Magento\Framework\Serialize\SerializerInterface::class);
            return $serializer->serialize($data);
        }
        return $data;
    }
}
