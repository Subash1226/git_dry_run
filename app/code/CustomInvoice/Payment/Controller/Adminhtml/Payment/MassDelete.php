<?php

namespace CustomInvoice\Payment\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use CustomInvoice\Payment\Model\PaymentFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package CustomInvoice\Payment\Controller\Adminhtml\Payment
 */
class MassDelete extends Action
{
    /**
     * @var PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param PaymentFactory $paymentFactory
     */
    public function __construct(
        Context $context,
        PaymentFactory $paymentFactory
    ) {
        $this->_paymentFactory = $paymentFactory;
        parent::__construct($context);
    }

    /**
     * Execute mass delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        $selectedIds = $this->getRequest()->getParam('selected', []);
        
        if (empty($selectedIds)) {
            $this->messageManager->addErrorMessage(__('Please select item(s) to delete.'));
            return $resultRedirect->setPath('custominvoice/payment/index');
        }

        try {
            $deletedCount = 0;
            foreach ($selectedIds as $selectedId) {
                $payment = $this->_paymentFactory->create()->load($selectedId);
                $payment->delete();
                $deletedCount++;
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $deletedCount)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('custominvoice/payment/index');
    }

    /**
     * Check if allowed to manage payment
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CustomInvoice_Payment::payment_delete');
    }
}