<?php

namespace MedizinhubCore\Lab\Block;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Customform content block
 */
class Booking extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Booking constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    public function getProductCollectionByCategories($ids)
    {
        try {
            $this->logger->info('Fetching product collection for categories: ' . implode(',', $ids));
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $ids]);
            return $collection;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching product collection: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCurrentUserId()
    {
        try {
            $userId = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomerId() : null;
            $this->logger->info('Current user ID: ' . ($userId ?? 'Guest'));
            return $userId;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching current user ID: ' . $e->getMessage());
            return null;
        }
    }

    public function getCurrentUserDetails()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->logger->info('No user is logged in.');
            return null;
        }

        try {
            $userId = $this->getCurrentUserId();
            $customer = $this->customerRepository->getById($userId);

            $userDetails = [
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'email' => $customer->getEmail(),
                'mobile' => $customer->getCustomAttribute('mobile_number')
                    ? $customer->getCustomAttribute('mobile_number')->getValue()
                    : 'Not Provided',
            ];

            $this->logger->info('Fetched user details: ' . json_encode($userDetails));
            return $userDetails;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching current user details: ' . $e->getMessage());
            return null;
        }
    }

    public function getUserDetailsJson()
    {
        try {
            $userDetails = $this->getCurrentUserDetails();

            if ($userDetails && isset($userDetails['name'])) {
                $userDetails['name'] = ucwords(strtolower($userDetails['name']));
            }

            $json = $userDetails ? json_encode($userDetails) : '{}';
            $this->logger->info('User details JSON: ' . $json);
            return $json;
        } catch (\Exception $e) {
            $this->logger->error('Error generating user details JSON: ' . $e->getMessage());
            return '{}';
        }
    }
    
    public function getMaxDiscount($category)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('health_package');
        
        try {
            $labTestData = $connection->fetchAll(
                "SELECT price, special_price FROM $tableName WHERE category = :category", 
                ['category' => $category]
            );
            
            $maxDiscount = 0;
            
            foreach ($labTestData as $test) {
                if (!empty($test['price']) && 
                    !empty($test['special_price']) && 
                    floatval($test['price']) > 0
                ) {
                    $discountPercentage = round((($test['price'] - $test['special_price']) / $test['price']) * 100);
                    $maxDiscount = max($maxDiscount, $discountPercentage);
                }
            }
            
            return $maxDiscount;
        } catch (\Exception $e) {
            $this->logger->error("Error in getMaxDiscount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function renderMaxDiscountLabel($category)
    {
        $maxDiscount = $this->getMaxDiscount($category);
        return 'Upto ' . ($maxDiscount > 0 ? $maxDiscount . '% OFF' : '15% OFF');
    }

}
