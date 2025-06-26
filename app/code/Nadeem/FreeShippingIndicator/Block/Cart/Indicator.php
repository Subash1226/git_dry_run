<?php
/**
 * Block/Cart/Indicator.php
 */
declare(strict_types=1);

namespace Nadeem\FreeShippingIndicator\Block\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Nadeem\FreeShippingIndicator\Helper\Data;

class Indicator extends Template
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $session
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $session,
        PriceCurrencyInterface $priceCurrency,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->session = $session;
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
    }

   /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return (bool)$this->helper->isEnable();
    }
    /**
     * Get minimum order total required for free shipping
     *
     * @return float
     */
    public function getFreeShippingMinValue(): float
    {
        $isModuleEnabled = $this->helper->isEnable();
        $freeShippingMethodConfig = $this->helper->getCoreShippingConfig();
        $orderMinTotal = $this->helper->getOrderMinTotal();

        if ($isModuleEnabled && $freeShippingMethodConfig) {
            return (float)$this->getFreeShippingMethodMinValue();
        }
        return (float)$orderMinTotal;
    }

    /**
     * Get free shipping method minimum value
     *
     * @return float
     */
    public function getFreeShippingMethodMinValue(): float
    {
        return (float)$this->helper->getCoreFreeShippingSubtotal();
    }

    /**
     * Get current cart total
     *
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentTotal(): float
    {
        $quote = $this->session->getQuote();
        if (!$quote->getId()) {
            return 0.0;
        }

        $useOrderSubtotal = $this->helper->getOrderSubtotal();
        $orderTotalWithDiscount = $this->helper->getOrderSubtotalWithDiscount();

        // Calculate total based on configuration
        if (!$useOrderSubtotal) {
            // Use grand total excluding shipping and tax if configured
            $total = $quote->getGrandTotal() - $quote->getShippingAmount();
            if (!$this->helper->getIncludeTax()) {
                $total -= $quote->getTaxAmount();
            }
        } elseif ($orderTotalWithDiscount) {
            // Use subtotal with discount
            $total = $quote->getSubtotalWithDiscount();
        } else {
            // Use base subtotal
            $total = $quote->getSubtotal();
        }

        return (float)max(0, $total);
    }

    /**
     * Check if order is eligible for free shipping
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isOrderEligibleForFreeShipping(): bool
    {
        if (!$this->isModuleEnabled()) {
            return false;
        }

        $currentTotal = $this->getCurrentTotal();
        $minValue = $this->getFreeShippingMinValue();

        return $currentTotal >= $minValue;
    }

    /**
     * Format price
     *
     * @param float $price
     * @param int $precision
     * @return string
     */
    public function getFormattedPrice(float $price, int $precision = 2): string
    {
        return $this->priceCurrency->format($price, false, $precision);
    }

    /**
     * Get remaining amount for free shipping
     *
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFreeShippingAmountDifference(): float
    {
        $currentTotal = $this->getCurrentTotal();
        $minValue = $this->getFreeShippingMinValue();

        if ($currentTotal >= $minValue) {
            return 0.0;
        }

        return max(0, $minValue - $currentTotal);
    }

    /**
     * Get completion percentage
     *
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFreeShippingCompletionRate(): float
    {
        $currentTotal = $this->getCurrentTotal();
        $minValue = $this->getFreeShippingMinValue();

        if ($minValue <= 0) {
            return 100.0;
        }

        $percentage = ($currentTotal / $minValue) * 100;
        return min(100, max(0, $percentage));
    }

    /**
     * Get font size
     *
     * @return string
     */
    public function getFontSize(): string
    {
        return $this->helper->getFontSize() ?: "14";
    }

    /**
     * Get text message
     *
     * @return string
     */
    public function getTextMessage(): string
    {
        return $this->helper->getTextMessage() ?: "To get FREE SHIPPING, add ";
    }

    /**
     * Get message background color
     *
     * @return string
     */
    public function getMessageBackground(): string
    {
        return $this->helper->getMessageBackground() ?: "#ff5501";
    }

    /**
     * Get progress bar color
     *
     * @return string
     */
    public function getProgressBarColor(): string
    {
        return $this->helper->getProgressBarColor() ?: "red";
    }

    /**
     * Get custom CSS
     *
     * @return string
     */
    public function getCustomCSS(): string
    {
        return $this->helper->getCustomCSS() ?: "";
    }

    /**
     * Get message text color
     *
     * @return string
     */
    public function getMessageTextColor(): string
    {
        return $this->helper->getMessageTextColor() ?: "white";
    }

    /**
     * Get eligible text message
     *
     * @return string
     */
    public function getEligibleTextMessage(): string
    {
        return $this->helper->getEligibleTextMessage()
            ?: "Your order is eligible for FREE SHIPPING.";
    }

    /**
     * Get cart data for AJAX response
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartData(): array
    {
        return [
            'success' => true,
            'current_total' => $this->getCurrentTotal(),
            'min_value' => $this->getFreeShippingMinValue(),
            'remaining_amount' => $this->getFreeShippingAmountDifference(),
            'formatted_remaining' => $this->getFormattedPrice(
                $this->getFreeShippingAmountDifference()
            ),
            'completion_rate' => $this->getFreeShippingCompletionRate(),
            'is_eligible' => $this->isOrderEligibleForFreeShipping(),
            'messages' => [
                'eligible' => $this->getEligibleTextMessage(),
                'remaining' => $this->getTextMessage()
            ],
            'styles' => [
                'font_size' => $this->getFontSize(),
                'background' => $this->getMessageBackground(),
                'text_color' => $this->getMessageTextColor(),
                'progress_color' => $this->getProgressBarColor(),
                'custom_css' => $this->getCustomCSS()
            ]
        ];
    }
}
