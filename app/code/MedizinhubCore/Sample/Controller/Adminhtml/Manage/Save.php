<?php

namespace MedizinhubCore\Sample\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use MedizinhubCore\Sample\Model\ManageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends Action
{
    protected $pageFactory;
    protected $manageFactory;
    protected $uploaderFactory;
    protected $filesystem;
    protected $directoryList;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ManageFactory $manageFactory,
        UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        DirectoryList $directoryList
    ) {
        $this->pageFactory = $pageFactory;
        $this->manageFactory = $manageFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('*/*/');
            return;
        }

        try {
            $model = $this->manageFactory->create();
            $id = $this->getRequest()->getParam('test_id');

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This Entry no longer exists.'));
                    $this->_redirect('*/*/');
                    return;
                }
            }

            $imageRequest = $this->getRequest()->getFiles('products_image');
            if (isset($imageRequest) && isset($imageRequest['name']) && $imageRequest['name'] != '') {
                try {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'products_image']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);

                    $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
                    $target = $mediaDirectory->getAbsolutePath('app/code/MedizinhubCore/Sample/view/adminhtml/web/images');

                    $result = $uploader->save($target);
                    $data['products_image'] = $result['file'];
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            } else {
                if (isset($data['products_image']['value'])) {
                    if (isset($data['products_image']['delete'])) {
                        $data['products_image'] = '';
                    } else {
                        $data['products_image'] = $data['products_image']['value'];
                    }
                }
            }

            $model->setData($data);
            $model->save();

            $this->messageManager->addSuccessMessage(__('You saved the Entry.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('test_id')]);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MedizinhubCore_Sample::save');
    }
}