<?php

namespace File\Upload\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Filesystem\Io\File;

class MediaManagement implements \File\Upload\Api\MediaManagementInterface
{
    protected $uploaderFactory;
    protected $filesystem;
    protected $resource;
    protected $addressRepository;
    protected $file;

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ResourceConnection $resource,
        AddressRepositoryInterface $addressRepository,
        File $file
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->resource = $resource;
        $this->addressRepository = $addressRepository;
        $this->file = $file;
    }

    public function upload($customerId, $addressId)
    {
        try {
            if (empty($customerId) || !is_numeric($customerId)) {
                throw new LocalizedException(__('Invalid customer ID.'));
            }
            $customerId = (int) $customerId;

            if (empty($addressId) || !is_numeric($addressId)) {
                throw new LocalizedException(__('Invalid address ID.'));
            }
            $addressId = (int) $addressId;

            if (empty($_FILES['file'])) {
                throw new LocalizedException(__('No files were uploaded.'));
            }

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $target = 'quick/order/';
            $uploadedFiles = [];

            foreach ($_FILES['file']['name'] as $key => $name) {
                if ($_FILES['file']['error'][$key] === UPLOAD_ERR_OK) {
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

                    $uploadedFiles[] = $target . $result['file'];
                }
            }

            $filePaths = implode(',', $uploadedFiles);

            $address = $this->addressRepository->getById($addressId);
            $mobileNumber = $address ? $address->getTelephone() : null;

            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('quick_order');
            $data = [
                'customer_id' => $customerId,
                'image' => $filePaths,
                'status' => '1',
                'created_at' => (new \DateTime('now', new \DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime('now', new \DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s'),
                'mobile_number' => $mobileNumber,
                'address_entity' => $addressId
            ];
            $connection->insert($tableName, $data);

            return [
                'message' => 'Order created successfully',
                'data' => [
                    'customerId' => $customerId,
                    'fileNames' => $uploadedFiles,
                    'addressId' => $addressId
                ]
            ];
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
