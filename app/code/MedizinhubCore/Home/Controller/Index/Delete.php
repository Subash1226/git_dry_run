<?php
namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\AddressFactory;

class Delete extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddressFactory $addressFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Execute the action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $addressId = (int) $this->getRequest()->getParam('id');

        try {
            // Load the address model
            $address = $this->addressFactory->create()->load($addressId);

            // Check if the address exists
            if (!$address->getId()) {
                throw new \Exception('Address not found.');
            }

            // Delete the address
            $address->delete();

            // Return success response
            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            // Return error response
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
