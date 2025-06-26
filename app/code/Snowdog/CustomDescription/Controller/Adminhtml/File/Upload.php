<?php

namespace Snowdog\CustomDescription\Controller\Adminhtml\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory as FileUploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Controller\ResultFactory;
use Snowdog\CustomDescription\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Upload
 * @package Snowdog\CustomDescription\Controller\File
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends Action
{
    /**
     * @var FileUploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Upload constructor.
     * @param Context $context
     * @param FileUploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param IoFile $ioFile
     * @param AdapterFactory $adapterFactory
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        IoFile $ioFile,
        AdapterFactory $adapterFactory,
        Data $helper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->adapterFactory = $adapterFactory;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = [];
        try {
            /** @var Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('product', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

            $uploadPath = $mediaDirectory->getAbsolutePath(Data::IMAGES_UPLOAD);
            $result = $uploader->save($uploadPath);

            if (!empty($result['file'])) {
                $result['url'] = $this->helper->getImageUrl($result['file']);
                $result['cookie'] = [
                    'name' => $this->_getSession()->getName(),
                    'value' => $this->_getSession()->getSessionId(),
                    'lifetime' => $this->_getSession()->getCookieLifetime(),
                    'path' => $this->_getSession()->getCookiePath(),
                    'domain' => $this->_getSession()->getCookieDomain(),
                ];

                // Handle new fields here (e.g., save metadata)
                $this->handleAdditionalFields($result);
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * Handles the additional fields after file upload
     *
     * @param array $result
     */
    private function handleAdditionalFields(array &$result): void
    {
        $ $params = $this->getRequest()->getParams();

        $expiryDate = $params['expiry_date'] ?? null;
        $price = $params['price'] ?? null;
        $specialPriceFromDate = $params['special_price_from_date'] ?? null;
        $specialPriceToDate = $params['special_price_to_date'] ?? null;
        $purchaseRate = $params['purchase_rate'] ?? null;
        $quantity = $params['quantity'] ?? null;
        $purchaseQuantity = $params['purchase_quantity'] ?? null;

        $result['expiry_date'] = $expiryDate;
        $result['price'] = $price;
        $result['special_price_from_date'] = $specialPriceFromDate;
        $result['special_price_to_date'] = $specialPriceToDate;
        $result['purchase_rate'] = $purchaseRate;
        $result['quantity'] = $quantity;
        $result['purchase_quantity'] = $purchaseQuantity;
    }
}
