<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Sp\Orderattachment\Block\Checkout;

use \Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Sp\Orderattachment\Model\Attachment;
use Sp\Orderattachment\Helper\Data;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Sp\Orderattachment\Helper\Data
     */
    protected $dataHelper;

    /**
     * LayoutProcessor constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerSession $customerSession
     * @param Data $dataHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        Data $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws Exception
     */
    public function process($jsLayout)
    {
        if ($this->dataHelper->isOrderAttachmentEnabled()) {
            switch ($this->scopeConfig->getValue(Attachment::XML_PATH_ATTACHMENT_ON_DISPLAY_ATTACHMENT, ScopeInterface::SCOPE_STORE)) {
                case 'after-payment-methods':
                    $this->addToAfterPaymentMethods($jsLayout);
                    break;
                case 'after-shipping-address':
                    $this->addToAfterShippingAddress($jsLayout);
                    break;
                case 'after-shipping-methods':
                    $this->addToAfterShippingMethods($jsLayout);
                    break;
                case 'new-step':
                    $this->addCheckoutStep($jsLayout);
                    break;
            }
        }

        return $jsLayout;
    }

    protected function addToAfterPaymentMethods(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['afterMethods']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['afterMethods']['children'];

            $fields['order-attachment-after-payment-methods'] = ['component' => "Sp_Orderattachment/js/view/order/payment/payment-attachment"];
        }

        return $jsLayout;
    }

    protected function addToAfterShippingAddress(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $fields['order-attachment-after-shipment-address'] =
                [
                    'component' => "Sp_Orderattachment/js/view/order/shipment/shipment-attachment"
                ];
        }

        return $jsLayout;
    }

    protected function addToAfterShippingMethods(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children'];

            $fields['order-attachment-after-shipment-methods'] =
                [
                    'component' => "uiComponent",
                    'displayArea' => "shippingAdditional",
                    'children' => ['attachment' => ['component' => "Sp_Orderattachment/js/view/order/shipment/shipment-attachment"]]
                ];
        }
        return $jsLayout;
    }
    protected function addCheckoutStep(&$jsLayout)
    {
    $prescriptionRequired = false;
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
    $items = $cart->getQuote()->getAllItems();
    foreach ($items as $item) {
        $_product = $objectManager->create('\Magento\Catalog\Model\Product')->load($item->getProductId());
        $prescriptionCheck = $_product->getData('prescription_check');
        if ($prescriptionCheck == 37) {
            $prescriptionRequired = true;
            break;
        }
    }
    $jsLayout['components']['checkout']['children']['steps']['children']['new-step'] = [
        'component' => 'Sp_Orderattachment/js/view/order/shipment/new-step',
        'config' => [
            'js' => 'Sp\Orderattachment\view\frontend\web\js\view\order\shipment\new-step.js'
        ],
        'prescriptionRequired' => $prescriptionRequired
    ];
    return $jsLayout;
    }
}
