<?php

namespace MedizinhubCore\Sample\Controller\Adminhtml\Manage;
/**
 * Class MassDelete
 * @package MedizinhubCore\SampleController\Adminhtml\Manage
 */
class MassDelete extends \Magento\Backend\App\Action
{
    protected $_manageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MedizinhubCore\Sample\Model\ManageFactory $manageFactory
    )
    {
        $this->_manageFactory = $manageFactory;
        parent::__construct($context);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $selectedIds = $data['selected'];
        try {
            foreach ($selectedIds as $selectedId) {
                $deleteData = $this->_manageFactory->create()->load($selectedId);
                $deleteData->delete();
            }
            $this->messageManager->addSuccess(__('Row data has been successfully deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('labtest/manage/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MedizinhubCore_Sample::delete');
    }
}
