<?php
namespace MedizinhubCore\Offers\Plugin\SalesRule\Model;

use Magento\SalesRule\Model\Rule;

class RulePlugin
{
    /**
     * Add additional fields to the rule model
     *
     * @param Rule $subject
     * @param array $data
     * @return array
     */
    public function beforeLoadPost(Rule $subject, array $data)
    {
        // Add custom fields to the data
        if (isset($data['coupon_image'])) {
            $subject->setData('coupon_image', $data['coupon_image']);
        }
        
        if (isset($data['coupon_titles'])) {
            $subject->setData('coupon_titles', $data['coupon_titles']);
        }
        
        if (isset($data['coupon_descriptions'])) {
            $subject->setData('coupon_descriptions', $data['coupon_descriptions']);
        }
        
        if (isset($data['coupon_category'])) {
            $subject->setData('coupon_category', $data['coupon_category']);
        }
        
        return [$data];
    }
}