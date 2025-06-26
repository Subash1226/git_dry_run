<?php
namespace Checkout\PrescriptionApi\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory;
use Psr\Log\LoggerInterface;

class UploadMediaManagement implements \Checkout\PrescriptionApi\Api\UploadMediaManagementInterface
{
    protected $uploaderFactory;
    protected $filesystem;
    protected $resource;
    protected $addressRepository;
    protected $file;
    protected $imageFactory;
    protected $logger;

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ResourceConnection $resource,
        AddressRepositoryInterface $addressRepository,
        File $file,
        AdapterFactory $imageFactory,
        LoggerInterface $logger
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->resource = $resource;
        $this->addressRepository = $addressRepository;
        $this->file = $file;
        $this->imageFactory = $imageFactory;
        $this->logger = $logger;
    }

    public function upload($orderId, $cartId)
    {
        try {
            if (empty($orderId) || !is_numeric($orderId)) {
                throw new LocalizedException(__('Invalid order ID.'));
            }
            $orderId = (int) $orderId;

            if (empty($cartId) || !is_numeric($cartId)) {
                throw new LocalizedException(__('Invalid cart ID.'));
            }
            $cartId = (int) $cartId;

            if (empty($_FILES['file'])) {
                throw new LocalizedException(__('No files were uploaded.'));
            }

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $uploadedFiles = [];

            foreach ($_FILES['file']['name'] as $key => $name) {
                if ($_FILES['file']['error'][$key] === UPLOAD_ERR_OK) {
                    $firstLetter = strtolower($name[0]);
                    $secondLetter = strtolower($name[1]);
                    $target = '/' . $firstLetter . '/' . $secondLetter . '/';

                    $uploader = $this->uploaderFactory->create(['fileId' => [
                        'name' => $name,
                        'type' => $_FILES['file']['type'][$key],
                        'tmp_name' => $_FILES['file']['tmp_name'][$key],
                        'error' => $_FILES['file']['error'][$key],
                        'size' => $_FILES['file']['size'][$key]
                    ]]);

                    $uploader->setAllowedExtensions(['jpg', 'pdf', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);

                    $result = $uploader->save($mediaDirectory->getAbsolutePath($target));

                    if (!$result) {
                        throw new LocalizedException(__('File cannot be saved.'));
                    }

                    $filePath = $mediaDirectory->getAbsolutePath($target) . $result['file'];
                    $fileName = $result['file'];

                    // Resize image if larger than 1MB
                    if ($_FILES['file']['size'][$key] > 1024 * 1024) {
                        $this->resizeImage($filePath);
                    }

                    // Convert file to base64
                    $fileContent = $this->file->read($filePath);
                    $base64Image = base64_encode($fileContent);

                    // Insert image details into sp_orderattachment table
                    $connection = $this->resource->getConnection();
                    $tableName = $this->resource->getTableName('sp_orderattachment');
                    $data = [
                        'quote_id' => $cartId,
                        'order_id' => $orderId,
                        'path' => $target . $fileName,
                        'hash' => $base64Image,
                        'uploaded_at' => (new \DateTime('now', new \DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s'),
                        'modified_at' => (new \DateTime('now', new \DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s'),
                    ];
                    $connection->insert($tableName, $data);

                    $uploadedFiles[] = $target . $fileName;
                }
            }

            return [
                'message' => 'Order created successfully',
                'data' => [
                    'orderId' => $orderId,
                    'fileNames' => $uploadedFiles,
                    'cartId' => $cartId
                ]
            ];
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    private function resizeImage($filePath)
    {
        $image = $this->imageFactory->create();
        $image->open($filePath);
        $maxWidth = 800; // Set your max width
        $maxHeight = 800; // Set your max height
        $image->constrainOnly(true);
        $image->keepAspectRatio(true);
        $image->keepFrame(false);
        $image->resize($maxWidth, $maxHeight);
        $image->save($filePath);
    }
}
