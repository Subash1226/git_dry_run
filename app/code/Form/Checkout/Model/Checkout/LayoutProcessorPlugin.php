<?php
namespace Form\Checkout\Model\Checkout;
use Magento\Framework\Session\SessionManagerInterface;

class LayoutProcessorPlugin
{
    
    private $sessionManager;
    public function __construct(
        SessionManagerInterface $sessionManager,
    ) {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        // Remove the existing street group configuration
        unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['street']);

        // Add 'Flat, House No' field directly
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['0'] = [
            'label' => __('Flat, House No'),
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.street.0',
            'provider' => 'checkoutProvider',
            'validation' => ['required-entry' => true, 'min_text_length' => 1, 'max_text_length' => 255],
            'additionalClasses' => 'shipping_form_houseno',
            'sortOrder' => 140
        ];

        // Add 'Street, Area' field directly
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['1'] = [
            'label' => __('Street, Area'),
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.street.1',
            'provider' => 'checkoutProvider',
            'validation' => ['required-entry' => true, 'min_text_length' => 1, 'max_text_length' => 255],
            'additionalClasses' => 'shipping_form_street_area',
            'sortOrder' => 150
        ];

        $mobileNumber = $this->sessionManager->getMobileNumber();

        if ($mobileNumber && strlen($mobileNumber) > 10) {
            $mobileNumber = substr($mobileNumber, 2);
        }
        if (!empty($mobileNumber)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['value'] = $mobileNumber;
        }


        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['value'] = $mobileNumber;

        return $jsLayout;
    }
}
