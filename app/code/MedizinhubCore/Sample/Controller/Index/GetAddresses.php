<?php
namespace MedizinhubCore\Sample\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class GetAddresses extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $resourceConnection;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => '', 'addresses' => ''];

        if (!$this->customerSession->isLoggedIn()) {
            $result['message'] = 'User not logged in';
            return $this->resultJsonFactory->create()->setData($result);
        }

        $customerId = $this->customerSession->getCustomerId();

        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('patient');
            $query = $connection->select()
                                ->from($tableName)
                                ->where('customer_id = ?', $customerId)
                                ->order('id DESC');

            $patients = $connection->fetchAll($query);
            $formattedAddresses = '';
            $firstAddress = true;

            foreach ($patients as $patient) {
                $checked = $firstAddress ? 'checked' : '';
                $address = htmlspecialchars($patient['street'], ENT_QUOTES, 'UTF-8');

                $formattedAddresses .= '<div class="address-preview" data-address-id="' . htmlspecialchars($patient['id'], ENT_QUOTES, 'UTF-8') . '">';
                $formattedAddresses .= '<hr class="address-break-line"><br>';
                $formattedAddresses .= '<input type="radio" name="address_entity" value="' . htmlspecialchars($patient['id'], ENT_QUOTES, 'UTF-8') . '" data-address-id="' . htmlspecialchars($patient['id'], ENT_QUOTES, 'UTF-8') . '" ' . $checked . '>';
                $formattedAddresses .= '<b>';
                $formattedAddresses .= htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8') . ' ';
                $formattedAddresses .= '(' . htmlspecialchars($patient['age'], ENT_QUOTES, 'UTF-8') . '), ';
                $formattedAddresses .= '</b>';
                $formattedAddresses .= htmlspecialchars($patient['house_no'], ENT_QUOTES, 'UTF-8') . ', ';
                $formattedAddresses .= htmlspecialchars($patient['street'], ENT_QUOTES, 'UTF-8') . ', ';
                $formattedAddresses .= htmlspecialchars($patient['area'], ENT_QUOTES, 'UTF-8') . ', ';
                $formattedAddresses .= htmlspecialchars($patient['city'], ENT_QUOTES, 'UTF-8') . ', ';
                $formattedAddresses .= htmlspecialchars($patient['postcode'], ENT_QUOTES, 'UTF-8') . '.<br>';
                $formattedAddresses .= '<div class="address-second-row">';
                $formattedAddresses .= 'Ph. No : ' . htmlspecialchars($patient['phone'], ENT_QUOTES, 'UTF-8') . '    ';
                $formattedAddresses .= '<a href="#" class="edit-address" data-telephone="' . htmlspecialchars($patient['phone'], ENT_QUOTES, 'UTF-8') . '" data-city="' . htmlspecialchars($patient['city'], ENT_QUOTES, 'UTF-8') . '" data-region-id="' . htmlspecialchars($patient['region_id'], ENT_QUOTES, 'UTF-8') . '" data-area="' . htmlspecialchars($patient['area'], ENT_QUOTES, 'UTF-8') . '" data-email="' . htmlspecialchars($patient['email'], ENT_QUOTES, 'UTF-8') . '" data-house-no="' . htmlspecialchars($patient['house_no'], ENT_QUOTES, 'UTF-8') . '"  data-address-id="' . htmlspecialchars($patient['id'], ENT_QUOTES, 'UTF-8') . '" data-customer-name="' . htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8') . '" data-age="' . htmlspecialchars($patient['age'], ENT_QUOTES, 'UTF-8') . '" data-street="' . htmlspecialchars($patient['street'], ENT_QUOTES, 'UTF-8') . '" data-gender="' . htmlspecialchars($patient['gender'], ENT_QUOTES, 'UTF-8') . '" data-pincode="' . htmlspecialchars($patient['postcode'], ENT_QUOTES, 'UTF-8') . '" data-phone="' . htmlspecialchars($patient['phone'], ENT_QUOTES, 'UTF-8') . '" data-whatsapp="' . htmlspecialchars($patient['whatsapp'], ENT_QUOTES, 'UTF-8') . '">Edit</a>';
                $formattedAddresses .= ' | ';
                $formattedAddresses .= '<a href="#" class="delete-address" data-address-id="' . htmlspecialchars($patient['id'], ENT_QUOTES, 'UTF-8') . '">Delete</a>';
                $formattedAddresses .= '</div><br>';
                $formattedAddresses .= '</div>';
                $firstAddress = false;
            }
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($formattedAddresses);

        } catch (\Exception $e) {
            $result['message'] = 'An error occurred while fetching addresses';
            $this->logger->error($e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
