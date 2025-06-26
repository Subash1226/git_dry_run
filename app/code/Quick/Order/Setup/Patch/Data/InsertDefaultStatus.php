<?php

namespace Quick\Order\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InsertDefaultStatus implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply the patch
     */
    public function apply()
    {
        $data = [
            ['status_code' => '0', 'status' => 'Order Cancelled'],
            ['status_code' => '1', 'status' => 'Order Placed'],
            ['status_code' => '2', 'status' => 'Order Under in Review'],
            ['status_code' => '3', 'status' => 'Order Accepted'],
            ['status_code' => '4', 'status' => 'Order Rejected']
        ];

        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('quick_order_status'),
            $data
        );
    }

    /**
     * Get aliases (previous names for the patch)
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get dependencies (other patches that must be executed before this one)
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
