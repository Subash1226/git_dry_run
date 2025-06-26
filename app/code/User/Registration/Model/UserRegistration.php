<?php
namespace User\Registration\Model;

use User\Registration\Api\UserRegistrationInterface;
use User\Registration\Api\Data\CustomerDataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Exception\InputException;

class UserRegistration implements UserRegistrationInterface
{
    protected $customerRepository;
    protected $customerFactory;
    protected $accountManagement;
    protected $resource;
    protected $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        AccountManagementInterface $accountManagement,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function createCustomer(CustomerDataInterface $customerData, $password)
    {
        try {
            // Validate customer data
            $this->validateCustomerData($customerData, $password);

            // Create customer
            $customer = $this->customerFactory->create();
            $customer->setEmail($customerData->getEmail());
            $customer->setFirstname($customerData->getFirstname());
            $customer->setLastname($customerData->getLastname());
            $customer->setStoreId($customerData->getStoreId());

            // Create account
            $newCustomer = $this->accountManagement->createAccount($customer, $password);
            $customerId = $newCustomer->getId();

            // Save mobile number in customer_entity_varchar
            if ($customerData->getMobileNumber()) {
                $this->saveMobileNumber($customerId, $customerData->getMobileNumber());
            }

            $response = [
                'success' => true,
                'message' => 'Customer created successfully.',
            ];
            return json_encode($response);

        } catch (LocalizedException $e) {
            $this->logger->error('Failed to create customer: ' . $e->getMessage());
            throw new WebapiException(__($e->getMessage()), 0, WebapiException::HTTP_BAD_REQUEST);
        } catch (CouldNotSaveException $e) {
            $this->logger->error('Could not save customer: ' . $e->getMessage());
            throw new WebapiException(__('Could not save customer: ' . $e->getMessage()), 0, WebapiException::HTTP_INTERNAL_ERROR);
        } catch (\Exception $e) {
            $this->logger->error('An error occurred while creating the customer: ' . $e->getMessage());
            throw new WebapiException(__('An error occurred while creating the customer.'), 0, WebapiException::HTTP_INTERNAL_ERROR);
        }
    }

    protected function saveMobileNumber($customerId, $mobileNumber)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('customer_entity_varchar');

        $attributeId = $this->getMobileNumberAttributeId();
        if ($attributeId) {
            $connection->insert($tableName, [
                'entity_id' => $customerId,
                'attribute_id' => $attributeId,
                'value' => '91'.$mobileNumber
            ]);
        }
    }

    protected function getMobileNumberAttributeId()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('eav_attribute'), 'attribute_id')
            ->where('attribute_code = ?', 'mobile_number')
            ->where('entity_type_id = ?', 1);

        return $connection->fetchOne($select);
    }

    private function validateCustomerData(CustomerDataInterface $customerData, $password)
    {
        if (empty($customerData->getEmail()) || !filter_var($customerData->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new InputException(__('Invalid email address.'));
        }

        if (empty($customerData->getFirstname()) || strlen($customerData->getFirstname()) < 0) {
            throw new InputException(__('First name is too short.'));
        }

        if (empty($customerData->getLastname()) || strlen($customerData->getLastname()) < 0) {
            throw new InputException(__('Last name is too short.'));
        }

        if (empty($customerData->getStoreId()) || !is_int($customerData->getStoreId())) {
            throw new InputException(__('Invalid store ID.'));
        }

        if (empty($customerData->getMobileNumber()) || !preg_match('/^\d{10}$/', $customerData->getMobileNumber())) {
            throw new InputException(__('Invalid mobile number.'));
        }

        if (empty($password) || strlen($password) < 6) {
            throw new InputException(__('Password must be at least 6 characters long.'));
        }
    }
}
