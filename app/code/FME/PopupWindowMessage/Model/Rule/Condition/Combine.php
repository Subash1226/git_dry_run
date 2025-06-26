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

namespace FME\PopupWindowMessage\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{

    /**
     * @var \FME\Geoipultimatelock\Model\Rule\Condition\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context                      $context
     * @param \FME\Geoipultimatelock\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \FME\PopupWindowMessage\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
    
        $this->_productFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType('FME\PopupWindowMessage\Model\Rule\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'FME\PopupWindowMessage\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
            [
                'value' => 'FME\PopupWindowMessage\Model\Rule\Condition\Combine',
                'label' => __('Conditions Combination'),
            ],
            ['label' => __('Product Attribute'), 'value' => $attributes]
                ]
        );
        
        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /**
 * @var Product|Combine $condition
*/
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
