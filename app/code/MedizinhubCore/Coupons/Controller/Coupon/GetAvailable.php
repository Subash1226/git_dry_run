<?php
namespace MedizinhubCore\Coupons\Controller\Coupon;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Framework\Exception\LocalizedException;

class GetAvailable extends Action
{
    protected $jsonFactory;
    protected $couponFactory;
    protected $ruleFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CouponFactory $couponFactory,
        RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        try {
            $coupons = $this->couponFactory->create()->getCollection();
            $couponData = [];
            
            foreach ($coupons as $coupon) {
                $rule = $this->ruleFactory->create()->load($coupon->getRuleId());
                
                if ($rule->getIsActive()) {
                    // Get coupon image if available
                    $imageUrl = '';
                    if ($rule->getData('coupon_image')) {
                        $imageUrl = $this->getImageUrl($rule->getData('coupon_image'));
                    }
                    
                    $couponData[] = [
                        'code' => $coupon->getCode(),
                        'couponName' => $rule->getName(),
                        'description' => $rule->getDescription(),
                        'image' => $imageUrl
                    ];
                }
            }
            
            return $result->setData(['success' => true, 'coupons' => $couponData]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => 'Unable to fetch coupons']);
        }
    }
    
    /**
     * Get coupon image URL
     *
     * @param string $imagePath
     * @return string
     */
    protected function getImageUrl($imagePath)
    {
        try {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . $imagePath;
        } catch (\Exception $e) {
            return '';
        }
    }
}