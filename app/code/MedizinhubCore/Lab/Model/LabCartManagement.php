<?php
namespace MedizinhubCore\Lab\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use MedizinhubCore\Lab\Api\LabCartManagementInterface;
use MedizinhubCore\Lab\Api\Data\LabCartInterface;
use MedizinhubCore\Lab\Api\Data\LabCartInterfaceFactory;
use MedizinhubCore\Lab\Api\Data\LabResponseInterface;
use MedizinhubCore\Lab\Api\Data\LabResponseInterfaceFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Authorization\Model\UserContextInterface;

class LabCartManagement implements LabCartManagementInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var LabCartInterfaceFactory
     */
    protected $labCartFactory;

    /**
     * @var LabResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param LabCartInterfaceFactory $labCartFactory
     * @param LabResponseInterfaceFactory $responseFactory
     * @param LoggerInterface $logger
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resource,
        LabCartInterfaceFactory $labCartFactory,
        LabResponseInterfaceFactory $responseFactory,
        UserContextInterface $userContext,
        Request $request,
        LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->resource = $resource;
        $this->labCartFactory = $labCartFactory;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->userContext = $userContext;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get current customer ID from API token
     *
     * @return int
     * @throws LocalizedException
     */
    private function getCurrentCustomerId(): int
    {
        $customerId = $this->userContext->getUserId();
        
        if (!$customerId || $this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new LocalizedException(
                __('Customer is not logged in'),
                null,
                401
            );
        }

        return $customerId;
    }

    /**
     * Check if customer is authorized for the operation
     *
     * @param int $requestedCustomerId
     * @return bool
     * @throws LocalizedException
     */
    private function isAuthorized(int $requestedCustomerId): bool
    {
        $currentCustomerId = $this->getCurrentCustomerId();
        
        if ($currentCustomerId !== $requestedCustomerId) {
            throw new LocalizedException(
                __('Not authorized to access this cart'),
                null,
                403
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function create(LabCartInterface $labCart)
    {
        $response = $this->responseFactory->create();

        try {
            $customerId = $this->getCurrentCustomerId();
            $labCart->setCustomerId($customerId);
            
            $this->validateLabCart($labCart);

            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('labcart');
            $testName = $labCart->getTestName();

            $existingTest = $this->checkExistingTest($connection, $tableName, $customerId, $testName);
            if ($existingTest) {
                return $response
                    ->setSuccess(false)
                    ->setMessage(__('The test "%1" is already in your cart.', $testName))
                    ->setErrorCode(409);
            }

            $data = [
                'customer_id' => $customerId,
                'test_name' => $testName,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $connection->insert($tableName, $data);
            $entityId = $connection->lastInsertId($tableName);
            $this->updateLabCartWithData($labCart, $entityId, $data);

            return $response
                ->setSuccess(true)
                ->setMessage(__('Lab cart created successfully'));

        } catch (LocalizedException $e) {
            $this->logger->error('Lab cart error: ' . $e->getMessage());
            return $response
                ->setSuccess(false)
                ->setMessage($e->getMessage())
                ->setErrorCode(401);
        } catch (ValidatorException $e) {
            $this->logger->error('Lab cart validation error: ' . $e->getMessage());
            return $response
                ->setSuccess(false)
                ->setMessage($e->getMessage())
                ->setErrorCode(400);
        } catch (\Exception $e) {
            $this->logger->critical('Error creating lab cart: ' . $e->getMessage());
            return $response
                ->setSuccess(false)
                ->setMessage(__('Unable to create lab cart. Please try again later.'))
                ->setErrorCode(500);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $response = $this->responseFactory->create();
        
        try {
            $customerId = $this->getCurrentCustomerId();
            
            $connection = $this->resource->getConnection();
            $labCartTable = $this->resource->getTableName('labcart');
            $healthPackageTable = $this->resource->getTableName('health_package');
            
            $select = $connection->select()
                ->from(
                    ['lc' => $labCartTable],
                    ['*']
                )
                ->joinLeft(
                    ['hp' => $healthPackageTable],
                    'hp.package_name = lc.test_name',
                    [
                        'package_id' => 'hp.id',
                        'special_price' => 'hp.special_price',
                        'price' => 'hp.price',
                        'image' => 'hp.image'
                    ]
                )
                ->where('lc.customer_id = ?', $customerId)
                ->where('lc.status = ?', 1);

            $items = $connection->fetchAll($select);

            if (empty($items)) {
                return $response
                    ->setSuccess(true)
                    ->setMessage(__('No items found in cart'))
                    ->setData([]);
            }

            return $response
                ->setSuccess(true)
                ->setData($items)
                ->setMessage(__('Lab cart items retrieved successfully'));

        } catch (LocalizedException $e) {
            return $response
                ->setSuccess(false)
                ->setMessage($e->getMessage())
                ->setErrorCode(401);
        } catch (\Exception $e) {        
            return $response
                ->setSuccess(false)
                ->setMessage(__('Unable to retrieve lab cart items. Please try again later.'))
                ->setErrorCode(500);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(LabCartInterface $labCart)
    {
        $response = $this->responseFactory->create();

        try {
            $customerId = $this->getCurrentCustomerId();
            
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('labcart');

            $result = $connection->update(
                $tableName,
                ['status' => 0],
                [
                    'customer_id = ?' => $customerId,
                    'status = ?' => 1
                ]
            );
            
            if (!$result) {
                return $response
                    ->setSuccess(false)
                    ->setMessage(__('No items found to update'))
                    ->setErrorCode(404);
            }
            
            $labCart->setCustomerId($customerId);
            $labCart->setStatus(0);

            return $response
                ->setSuccess(true)
                ->setMessage(__('Lab cart updated successfully'));

        } catch (LocalizedException $e) {
            return $response
                ->setSuccess(false)
                ->setMessage($e->getMessage())
                ->setErrorCode(401);
        } catch (\Exception $e) {
            $this->logger->critical('Error updating lab cart: ' . $e->getMessage());
            return $response
                ->setSuccess(false)
                ->setMessage(__('Unable to update lab cart. Please try again later.'))
                ->setErrorCode(500);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entityId)
    {
        $response = $this->responseFactory->create();

        if (!$entityId) {
            return $response
                ->setSuccess(false)
                ->setMessage(__('Entity ID is required'))
                ->setErrorCode(400);
        }

        try {
            $customerId = $this->getCurrentCustomerId();
            
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('labcart');

            $result = $connection->delete(
                $tableName,
                [
                    'entity_id = ?' => $entityId,
                    'customer_id = ?' => $customerId
                ]
            );
            
            if (!$result) {
                return $response
                    ->setSuccess(false)
                    ->setMessage(__('Item not found or unauthorized'))
                    ->setErrorCode(404);
            }

            return $response
                ->setSuccess(true)
                ->setMessage(__('Lab cart item deleted successfully'));

        } catch (LocalizedException $e) {
            return $response
                ->setSuccess(false)
                ->setMessage($e->getMessage())
                ->setErrorCode(401);
        } catch (\Exception $e) {
            $this->logger->critical('Error deleting lab cart: ' . $e->getMessage());
            return $response
                ->setSuccess(false)
                ->setMessage(__('Unable to delete lab cart item. Please try again later.'))
                ->setErrorCode(500);
        }
    }

    /**
     * Validates lab cart data
     *
     * @param LabCartInterface $labCart
     * @throws ValidatorException
     */
    protected function validateLabCart(LabCartInterface $labCart)
    {
        $errors = [];

        if (!$labCart->getCustomerId()) {
            $errors[] = __('Customer ID is required.');
        }

        if (!$labCart->getTestName()) {
            $errors[] = __('Test name is required.');
        } elseif (strlen($labCart->getTestName()) > 255) {
            $errors[] = __('Test name exceeds maximum length of 255 characters.');
        }

        if (!empty($errors)) {
            throw new ValidatorException(__(implode(' ', $errors)));
        }
    }

    /**
     * Check for existing test in cart
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $tableName
     * @param int $customerId
     * @param string $testName
     * @return array|bool
     */
    private function checkExistingTest($connection, $tableName, $customerId, $testName)
    {
        $select = $connection->select()
            ->from($tableName)
            ->where('customer_id = ?', $customerId)
            ->where('test_name = ?', $testName)
            ->where('status = ?', 1);

        return $connection->fetchRow($select);
    }

    /**
     * Update lab cart with data
     *
     * @param LabCartInterface $labCart
     * @param int $entityId
     * @param array $data
     * @return void
     */
    private function updateLabCartWithData($labCart, $entityId, $data)
    {
        $labCart->setEntityId($entityId);
        $labCart->setCustomerId($data['customer_id']);
        $labCart->setTestName($data['test_name']);
        $labCart->setStatus($data['status']);
        $labCart->setCreatedAt($data['created_at']);
    }
}