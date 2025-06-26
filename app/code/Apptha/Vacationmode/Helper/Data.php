<?php

namespace Apptha\Vacationmode\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * This class contains manipulation functions
 */
class Data extends AbstractHelper {
    const XML_VACATION_MODE = 'vacationstatus/seller/vacation_mode';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    protected $conficAttributeData;

    /**
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableProduct\Helper\Data $conficAttributeData
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Helper\Data $conficAttributeData
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->productFactory = $productFactory;
        $this->conficAttributeData = $conficAttributeData;
    }

    /**
     * Get Enable/disable Vacationmode
     *
     * @return string
     */
    public function getVacationMode() {
        return $this->scopeConfig->getValue(static::XML_VACATION_MODE, ScopeInterface::SCOPE_STORE);
    }
}
