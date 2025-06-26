<?php

namespace Mobile\Avaliable\Model;

use Mobile\Avaliable\Api\MobilenumberCheckInterface;
use Magento\Framework\App\ResourceConnection;

class MobilenumberCheck implements MobilenumberCheckInterface
{
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function checkMobilenumber($mobilenumber)
    {
        $connection = $this->resourceConnection->getConnection();
        $eavAttributeTable = $connection->getTableName('eav_attribute');
        $customerEntityVarcharTable = $connection->getTableName('customer_entity_varchar');
        $customerEntityTable = $connection->getTableName('customer_entity');

        // Get the attribute ID for the mobile number
        $selectAttributeId = $connection->select()
            ->from($eavAttributeTable, ['attribute_id'])
            ->where('attribute_code = ?', 'mobile_number')
            ->where('entity_type_id = ?', 1);

        $attributeId = $connection->fetchOne($selectAttributeId);

        // Check if a customer with the given mobile number exists
        $selectCustomerId = $connection->select()
            ->from($customerEntityVarcharTable, ['entity_id'])
            ->where('attribute_id = ?', $attributeId)
            ->where('value = ?', $mobilenumber);

        $customerId = $connection->fetchOne($selectCustomerId);

        if ($customerId) {
            // Get the customer's email ID
            $selectCustomerEmail = $connection->select()
                ->from($customerEntityTable, ['email'])
                ->where('entity_id = ?', $customerId);

            $customerEmail = $connection->fetchOne($selectCustomerEmail);

            return [
                'available' => true,
                'email' => $customerEmail
            ];
        }

        // Return false if the customer does not exist
        return ['available' => false];
    }
}