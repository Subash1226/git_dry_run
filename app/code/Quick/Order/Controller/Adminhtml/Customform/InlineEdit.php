<?php

declare(strict_types=1);

namespace Quick\Order\Controller\Adminhtml\Customform;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;
    protected $auth;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Backend\Model\Auth\Session $auth
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Backend\Model\Auth\Session $auth
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->auth = $auth;
    }


    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Quick\Order\Model\Customform $model */
                    $model = $this->_objectManager->create(\Quick\Order\Model\Customform::class)->load($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                        $model->save();

                        // Insert admin user ID into quick_order_review table
                        $adminUserId = $this->auth->getUser()->getId();
                        $connection = $model->getResource()->getConnection();
                        $tableName = $connection->getTableName('quick_order_review');
                        $data = [
                            'order_id' => $model->getId(),
                            'status' => $model->getStatus(),
                            'user_id' => $adminUserId
                        ];
                        $connection->insert($tableName, $data);
                    } catch (\Exception $e) {
                        $messages[] = "[Customform ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error,
            'admin_user_id' => $this->auth->getUser()->getId()
        ]);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Quick_Order::inlineedit');
    }
}
