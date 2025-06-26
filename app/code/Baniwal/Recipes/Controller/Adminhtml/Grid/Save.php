<?php
namespace Baniwal\Recipes\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Baniwal\Recipes\Model\GridFactory;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Framework\Stdlib\StringUtils;

class Save extends Action
{
    /**
     * @var GridFactory
     */
    protected $gridFactory;

    /**
     * @var WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var StringUtils
     */
    protected $string;

    public function __construct(
        Context $context,
        GridFactory $gridFactory,
        \Magento\Framework\Filesystem $filesystem,
        UrlRewriteFactory $urlRewriteFactory,
        StringUtils $string = null
    ) {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->string = $string ?: \Magento\Framework\App\ObjectManager::getInstance()->get(StringUtils::class);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        try {
            $rowData = $this->gridFactory->create();
            $isEdit = false;
            if (isset($_FILES['image']) && $_FILES['image']['name']) {
                $imageUploader = $this->_objectManager->create(
                    \Magento\MediaStorage\Model\File\Uploader::class,
                    ['fileId' => 'image']
                );
                $imageUploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $imageUploader->setAllowRenameFiles(true);
                $imageUploader->setFilesDispersion(true);

                $result = $imageUploader->save($this->_mediaDirectory->getAbsolutePath('health/packages'));

                if ($result) {
                    $data['image'] = 'health/packages' . $result['file'];
                }
            } else {
                if (isset($data['id'])) {
                    unset($data['image']);
                }
            }
            if (isset($data['id'])) {
                $rowData = $rowData->load($data['id']);
                $isEdit = true;
            }
            $packageName = isset($data['package_name'])
                ? $this->cleanUrlString($data['package_name'])
                : 'package';
            if (!$isEdit) {
                $collection = $this->gridFactory->create()->getCollection();
                $collection->setOrder('id', 'DESC');
                $lastItem = $collection->getFirstItem();
                $nextId = $lastItem->getId() ? $lastItem->getId() + 1 : 1;
                $requestPath = 'lab-test/package/' . $packageName;
            } else {
                $requestPath = 'lab-test/package/' . $packageName;
            }
            $data['packageurl'] = $requestPath;
            $rowData->setData($data);
            $rowData->save();
            $currentId = $rowData->getId();
            $targetPath = 'lab-test/package/index/id/' . $currentId;

            // Updated URL Rewrite Logic
            $urlRewrite = $this->urlRewriteFactory->create();
            $existingRewrite = $urlRewrite->getCollection()
                ->addFieldToFilter('request_path', $requestPath)
                ->getFirstItem();

            // Check if the URL rewrite already exists
            if (!$existingRewrite->getId()) {
                $urlRewrite->setStoreId(1)
                    ->setRequestPath($requestPath)
                    ->setTargetPath($targetPath)
                    ->setIsSystem(0)
                    ->save();
            } else {
                // If URL rewrite exists, update the existing one if the target path is different
                if ($existingRewrite->getTargetPath() !== $targetPath) {
                    $existingRewrite->setTargetPath($targetPath)
                        ->save();
                }
                // If the target path is the same, do nothing
            }

            $this->messageManager->addSuccess(__('Package data and URL rewrite have been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('grid/grid/index');
    }

    /**
     * Clean URL string
     *
     * @param string $string
     * @return string
     */
    protected function cleanUrlString($string)
    {
        $string = preg_replace('/[^A-Za-z0-9\-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = strtolower(trim($string, '-'));

        return $string;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Baniwal_Recipes::save');
    }
}
