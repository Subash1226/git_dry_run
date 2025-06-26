<?php
namespace MedizinhubCore\Offers\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;

/**
 * Customform content block for Sales Rules and Coupons
 */
class Offers extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }

    /**
    * Get sales rules filtered by category and current date
    *
    * @param string $category
    * @return array
    */
    private function getSalesRulesByCategory($category)
    {
        $connection = $this->resourceConnection->getConnection();
        $currentDate = date('Y-m-d');

        $select = $connection->select()
            ->from(
                ['sr' => $this->resourceConnection->getTableName('salesrule')],
                [
                    'rule_id',
                    'name',
                    'description',
                    'from_date',
                    'to_date',
                    'discount_type' => 'simple_action',
                    'discount_amount' => 'discount_amount',
                    'coupon_image',
                    'coupon_titles',
                    'coupon_descriptions'
                ]
            )
            ->join(
                ['sc' => $this->resourceConnection->getTableName('salesrule_coupon')],
                'sr.rule_id = sc.rule_id',
                [
                    'coupon_id',
                    'code',
                    'usage_limit',
                    'usage_per_customer'
                ]
            )
            ->where('sr.is_active = ?', 1)
            ->where('sr.coupon_category = ?', $category)
            ->where('(sr.from_date IS NULL OR sr.from_date <= ?)', $currentDate)
            ->where('(sr.to_date IS NULL OR sr.to_date >= ?)', $currentDate);

        return $connection->fetchAll($select);
    }

    /**
     * Get sales rules for medicine offers
     *
     * @return array
     */
    public function getMedicineOffers()
    {
        return $this->getSalesRulesByCategory('medicine_offers');
    }

    /**
     * Get sales rules for lab test offers
     *
     * @return array
     */
    public function getLabTestOffers()
    {
        return $this->getSalesRulesByCategory('lab_test_offers');
    }

    /**
     * Get sales rules for doctor consult offers
     *
     * @return array
     */
    public function getDoctorConsultOffers()
    {
        return $this->getSalesRulesByCategory('doctor_consult_offers');
    }

    public function getCouponDetails($ruleId)
    {
        $connection = $this->resourceConnection->getConnection();
        
        $select = $connection->select()
            ->from(['sr' => $this->resourceConnection->getTableName('salesrule')], 
                [
                    'coupon_titles',
                    'coupon_descriptions',
                    'description'
                ])
            ->where('sr.rule_id = ?', $ruleId);

        return $connection->fetchRow($select);
    }

    /**
     * Prepare layout
     * 
     * @return \Magento\Framework\View\Element\Template
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}