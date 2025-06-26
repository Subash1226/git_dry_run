<?php
namespace Quick\Order\Controller\Index;

use Magento\Framework\App\Action\Context;
use Quick\Order\Model\CustomformFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\CustomerFactory;
use Psr\Log\LoggerInterface; // Add this line to use the logger
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $_customform;
    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;
    protected $addressRepository;
    protected $customerRepository;
    protected $customerFactory;
    protected $logger; // Add this property
    protected $resultJsonFactory; // Add this property

    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomformFactory $customform,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        CustomerFactory $customerFactory,
        LoggerInterface $logger, // Add this parameter
        JsonFactory $resultJsonFactory // Add this parameter
    ) {
        $this->_customform = $customform;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger; // Initialize the logger
        $this->resultJsonFactory = $resultJsonFactory; // Initialize the result JSON factory
        parent::__construct($context);
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => ''];

        if (!$this->getRequest()->isPost()) {
            $result['message'] = 'Invalid request.';
            return $this->resultJsonFactory->create()->setData($result);
        }

        $data = $this->getRequest()->getPostValue();
        $data['customer_id'] = $this->getRequest()->getPostValue('customer_id');
        $data['order_id'] = $this->getRequest()->getPostValue('order_id');
        $data['status'] = $this->getRequest()->getPostValue('status');
        $addressId = isset($data['address_entity']) ? $data['address_entity'] : null;

        try {
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
                $uploadedFiles = $_FILES['image'];
                $filePaths = [];

                $this->logger->info('Uploaded Files: ' . print_r($uploadedFiles, true));

                foreach ($uploadedFiles['name'] as $key => $fileName) {
                    if ($uploadedFiles['error'][$key] == 0) {
                        $uploader = $this->uploaderFactory->create(['fileId' => 'image[' . $key . ']']);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf']);
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $destinationPath = $mediaDirectory->getAbsolutePath('quick/order');
                        $result = $uploader->save($destinationPath);

                        if ($result) {
                            $filePaths[] = 'quick/order/' . $result['file'];
                        } else {
                            throw new LocalizedException(__('File cannot be saved to path: %1', $destinationPath));
                        }
                    } else {
                        // Log the specific error code for the file
                        $this->logger->error('File upload error for file ' . $fileName . ': Error Code ' . $uploadedFiles['error'][$key]);
                    }
                }

                $data['image'] = implode(',', $filePaths);
            }

            $customerId = $data['customer_id'];
            $isDefaultBilling = true;
            $isDefaultShipping = true;
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getId()) {
                if ($isDefaultBilling) {
                    $customer->setDefaultBilling($data['address_entity']);
                }
                if ($isDefaultShipping) {
                    $customer->setDefaultShipping($data['address_entity']);
                }
                $customer->save();
            }

            if (empty($addressId)) {
                $connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
                $addressEntityData = $connection->fetchAll("SELECT entity_id FROM customer_address_entity WHERE parent_id ='" . $data['customer_id'] . "' ORDER BY entity_id DESC LIMIT 1");
                foreach ($addressEntityData as $addressEntityRow) {
                    $data['address_entity'] = $addressEntityRow['entity_id'];
                }
            } else {
                $connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
                $addressData = $connection->fetchRow("SELECT telephone FROM customer_address_entity WHERE entity_id = :address", ['address' => $addressId]);
                if (!empty($addressData)) {
                    $data['mobile_number'] = $addressData['telephone'];
                } else {
                    $messageManager = \Magento\Framework\App\ObjectManager::getInstance()->get(ManagerInterface::class);
                    $messageManager->addErrorMessage(__('No address found for the provided entity ID.'));
                }
            }

            $this->saveDataToDatabase($data);
            $result['success'] = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage()); // Log the error message
            $result['message'] = __('An Error occurred while uploading files: %1', $e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    private function saveDataToDatabase($data)
    {
        $customform = $this->_customform->create();
        $customform->setData($data);
        $customform->save();
    }
}
