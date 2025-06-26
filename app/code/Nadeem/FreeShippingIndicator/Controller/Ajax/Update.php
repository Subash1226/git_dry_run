<?php
/**
 * Controller/Ajax/Update.php
 */
declare(strict_types=1);

namespace Nadeem\FreeShippingIndicator\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Update implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param Session $checkoutSession
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        Session $checkoutSession,
        RequestInterface $request
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            // Verify the quote exists
            $quote = $this->checkoutSession->getQuote();
            if (!$quote || !$quote->getId()) {
                throw new LocalizedException(__('Cart not found.'));
            }

            // Create and render the block
            $page = $this->resultPageFactory->create();
            $block = $page->getLayout()
                ->createBlock(\Nadeem\FreeShippingIndicator\Block\Cart\Indicator::class)
                ->setTemplate('Nadeem_FreeShippingIndicator::cart/summary/indicator.phtml');

            // Get cart data and render HTML
            $cartData = $block->getCartData();
            $cartData['html'] = $block->toHtml();

            return $result->setData($cartData);

        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        } catch (NoSuchEntityException $e) {
            return $result->setData([
                'success' => false,
                'error' => __('Cart not found.')
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => __('An error occurred while updating the shipping indicator.')
            ]);
        }
    }
}
