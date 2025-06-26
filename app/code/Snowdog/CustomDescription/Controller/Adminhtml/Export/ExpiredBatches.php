<?php
namespace Snowdog\CustomDescription\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Snowdog\CustomDescription\Model\Export\ExpiredBatchesCsv;
use Magento\Framework\App\RequestInterface;

class ExpiredBatches extends Action
{
    protected $fileFactory;
    protected $expiredBatchesCsv;
    protected $request;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ExpiredBatchesCsv $expiredBatchesCsv,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->expiredBatchesCsv = $expiredBatchesCsv;
        $this->request = $request;
    }

    public function execute()
    {
        $productId = $this->request->getParam('product_id');

        // Ensure the  ID is provided
        if (!$productId) {
            $this->messageManager->addErrorMessage(__('Product ID is missing.'));
            return $this->_redirect('*/*/');
        }

        $this->expiredBatchesCsv->setProductId($productId);

        $currentDate = date('d-m-Y');
        $fileName = 'expired_batches_' . $currentDate . '.csv';
        $content = $this->expiredBatchesCsv->getCsvContent();

        return $this->fileFactory->create(
            $fileName,
            $content,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'text/csv'
        );
    }
}


