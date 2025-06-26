<?php
namespace MedizinhubCore\Lab\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class HealthPackage implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            // Get connection
            $connection = $this->resourceConnection->getConnection();

            // Select table name
            $tableName = $this->resourceConnection->getTableName('health_package');

            // Prepare select query
            $select = $connection->select()
                ->from($tableName);

            // Execute query and fetch all results
            $healthPackages = $connection->fetchAll($select);

            // Return JSON response
            return $result->setData([
                'success' => true,
                'health_packages' => $healthPackages
            ]);

        } catch (\Exception $e) {
            // Log the error
            $this->logger->critical($e->getMessage());

            // Return error response
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
