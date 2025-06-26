<?php
namespace Snowdog\CustomDescription\Model\Export;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface; // Import the LoggerInterface
use Magento\Framework\Controller\Result\JsonFactory;

class ExpiredBatchesCsv
{
    protected $resource;
    protected $dateTime;
    protected $resultJsonFactory;
    protected $productId;
    protected $logger; // Add a logger property

    public function __construct(
        ResourceConnection $resource,
        DateTime $dateTime,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger // Inject LoggerInterface
    ) {
        $this->resource = $resource;
        $this->dateTime = $dateTime;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger; // Assign the logger
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function getCsvContent()
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('snowdog_custom_description');

        // Directly use the hardcoded SQL query
        $query = "SELECT {$tableName}.*
                  FROM {$tableName}
                  WHERE (product_id = '{$this->productId}')
                  AND (expiry_status_option IN (3, 4) OR expiry_status IN (0, 5))";

        $this->logger->info('SQL Query: ' . $query);

        // Execute the query and fetch results
        $result = $connection->fetchAll($query);

        $csvData = [];
        $serialNo = 1;

        if (!empty($result)) {
            $headers = [
                'S.no',
                'Batch ID',
                'Purchase Quantity',
                'Current Qty',
                'Purchase Rate',
                'MRP',
                'Discount Price',
                'Discount Price From Date',
                'Discount Price To Date',
                'Expiry Date'
            ];
            $csvData[] = $headers;

            foreach ($result as $row) {
                $csvData[] = [
                    $serialNo++,                             // S.no
                    $row['title'],                           // Batch ID
                    $row['purchase_quantity'],               // Purchase Quantity
                    $row['quantity'],                        // Current Qty (assuming same as Purchase Qty)
                    $row['purchase_rate'],                   // Purchase Rate
                    $row['price'],                           // MRP (same as price)
                    $row['special_price'],                   // Discount Price
                    $row['special_price_from_date'],         // Discount Price From Date
                    $row['special_price_to_date'],           // Discount Price To Date
                    $row['expiry_date'],                     // Expiry Date
                ];
            }
        }

        $csvContent = '';
        foreach ($csvData as $line) {
            $csvContent .= implode(',', $line) . PHP_EOL;
        }

        $this->logger->info('CSV Content Generated: ' . $csvContent); // Log the CSV content for verification

        return $csvContent;
    }
}

