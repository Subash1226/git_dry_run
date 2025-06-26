<?php
namespace Baniwal\Recipes\Api;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception as WebapiException;
use MedizinhubCore\Lab\Api\Data\LabResponseInterface;
use MedizinhubCore\Lab\Api\Data\LabResponseInterfaceFactory;
use Baniwal\Recipes\Model\GridFactory;

class HealthPackagesApi implements HealthPackagesApiInterface
{
    /**
     * @var GridFactory
     */
    protected $gridFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    
    /**
     * @var LabResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LabResponseInterfaceFactory $responseFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GridFactory $gridFactory,
        LabResponseInterfaceFactory $responseFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->responseFactory = $responseFactory;
        $this->gridFactory = $gridFactory;
    }

    /**
     * Get all health packages
     *
     * @return array
     * @throws WebapiException
     */
    public function getAllHealthPackages()
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $response = $this->responseFactory->create();
            
            $query = $connection->select()->from('health_package');
            $result = $connection->fetchAll($query);
            
            if (empty($result)) {
                throw new LocalizedException(__('No health packages found'));
            }

            return $response
                ->setSuccess(true)
                ->setMessage(__('Package retrieved successfully'))
                ->setData($result);
            
        } catch (LocalizedException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch (\Exception $e) {
            throw new WebapiException(
                __('An error occurred while fetching health packages: %1', $e->getMessage()),
                0,
                WebapiException::HTTP_INTERNAL_ERROR
            );
        }
    }

    /**
     * Get package by id
     *
     * @param string $id
     * @return array
     * @throws WebapiException
     */
    public function getPackageByName($id)
    {
        try {
            // Validate input
            if (empty($id) || !is_string($id)) {
                throw new LocalizedException(__('Package id must be a non-empty string'));
            }
            
            // Sanitize input
            $id = trim($id);
            
            $rowData = $this->gridFactory->create();
            $result = $rowData->load($id);
            
            if (empty($result)) {
                throw new LocalizedException(__('No package found with id: %1', $id));
            }

            return $result;
            
        } catch (LocalizedException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch (\Exception $e) {
            throw new WebapiException(
                __('An error occurred while fetching package: %1', $e->getMessage()),
                0,
                WebapiException::HTTP_INTERNAL_ERROR
            );
        }
    }

    /**
     * Get package by category
     *
     * @param string $category
     * @return array
     * @throws WebapiException
     */
    public function getPackageByCategory($category)
    {
        try {
            // Validate input
            if (empty($category) || !is_string($category)) {
                throw new LocalizedException(__('Package Category must be a non-empty string'));
            }
            
            // Sanitize input
            $category = trim($category);
            
            $connection = $this->resourceConnection->getConnection();
            $response = $this->responseFactory->create();
            
            $query = $connection->select()
                ->from('health_package')
                ->where('category = ?', $category);
            
            $result = $connection->fetchAll($query);
            
            if (empty($result)) {
                return $response
                ->setSuccess(false)
                ->setMessage(__('No Package retrieved successfully'))
                ->setData($result);
            }

            return $response
                ->setSuccess(true)
                ->setMessage(__('Package retrieved successfully'))
                ->setData($result);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => sprintf('Package "%s" retrieved successfully', $category)
            ];
            
        } catch (LocalizedException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch (\Exception $e) {
            throw new WebapiException(
                __('An error occurred while fetching package: %1', $e->getMessage()),
                0,
                WebapiException::HTTP_INTERNAL_ERROR
            );
        }
    }
}