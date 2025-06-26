<?php
/**
 * Class AddCustomerAttributes
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_MobileNumberLogin
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\MobileNumberLogin\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddCustomerAttributes
    implements DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * Customer's mobile number attribute.
     */
    const MOBILE_NUMBER = 'mobile_number';

    /**
     * Customer's mobile number country code.
     */
    const COUNTRY_CODE = 'country_code';

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(Customer::ENTITY, self::MOBILE_NUMBER, [
            'label' => 'Mobile Number',
            'input' => 'text',
            'backend' => \Sparsh\MobileNumberLogin\Model\Attribute\Backend\MobileNumber::class,
            'required' => false,
            'sort_order' => 85,
            'position' => 85,
            'system' => false,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true
        ]);

        /** @var $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::MOBILE_NUMBER);

        $usedInForms = [
            'adminhtml_customer',
            'checkout_register',
            'customer_account_create',
            'customer_account_edit',
            'adminhtml_checkout'
        ];

        $attribute->setData('used_in_forms', $usedInForms);
        $attribute->save();

        $customerSetup->addAttribute(Customer::ENTITY, self::COUNTRY_CODE, [
            'label' => 'Country Code',
            'input' => 'text',
            'required' => false,
            'sort_order' => 84,
            'position' => 84,
            'system' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false
        ]);

        /** @var $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::COUNTRY_CODE);

        $usedInForms = [
            'adminhtml_customer',
            'checkout_register',
            'customer_account_create',
            'customer_account_edit',
            'adminhtml_checkout'
        ];

        $attribute->setData('used_in_forms', $usedInForms);
        $attribute->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [   
        ];
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        /**
         * This internal Magento method, that means that some patches with time can change their names,
         * but changing name should not affect installation process, that's why if we will change name of the patch
         * we will add alias here
         */
        return [];
    }
}