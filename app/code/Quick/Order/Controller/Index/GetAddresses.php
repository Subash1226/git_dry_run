<?php

namespace Quick\Order\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\AddressFactory;

class GetAddresses extends Action
{
    protected $resultJsonFactory;
    protected $addressFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddressFactory $addressFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $customerId = $this->customerSession->getCustomer()->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
        $result1 = $connection->fetchAll("SELECT entity_id, firstname, lastname, company, street, city, region, region_id, postcode, telephone FROM customer_address_entity WHERE parent_id = '" . $customerId . "' ORDER BY entity_id DESC");

        $formattedAddresses = '';
        $firstAddress = true;
        foreach ($result1 as $address) {
            $checked = $firstAddress ? 'checked' : '';
            $streetParts = json_decode($address['street'], true);
            $streetParts = explode("\n", $address['street']);
            $houseNo = isset($streetParts[0]) ? trim($streetParts[0]) : '';
            $streetName = isset($streetParts[1]) ? trim($streetParts[1]) : '';

            $formattedAddresses .= '<div class="address-preview" data-address-id="' . $address['entity_id'] . '">';
            $formattedAddresses .= '<hr class="address-break-line"><br>';
            $formattedAddresses .= '<input type="radio" name="address_entity" value="' . $address['entity_id'] . '" data-address-id="' . $address['entity_id'] . '" ' . $checked . '>';
            $formattedAddresses .= '<b>';
            $formattedAddresses .= $address['firstname'] . ' ';
            $formattedAddresses .= $address['lastname'] . ', ';
            $formattedAddresses .= '</b>';
            $formattedAddresses .= $address['company'] . ', ';
            $formattedAddresses .= $houseNo . ' ' . $streetName . ', ';
            $formattedAddresses .= $address['region'] . ', ';
            $formattedAddresses .= $address['city'] . ' ';
            $formattedAddresses .= $address['postcode'] . '.<br>';
            $formattedAddresses .= '<div class="address-second-row">';
            $formattedAddresses .= 'Ph. No : ' . $address['telephone'] . '    ';
            $formattedAddresses .= '<a href="#" class="edit-address" data-address-id="' . $address['entity_id'] . '" data-customer-name="' . $address['firstname'] . '" data-last-name="' . $address['lastname'] . '" data-company="' . $address['company'] . '" data-house-no="' . $houseNo . '" data-street="' . $streetName . '" data-city="' . $address['city'] . '" data-region="' . $address['region'] . '" data-region_id="' . $address['region_id'] . '" data-postcode="' . $address['postcode'] . '" data-landmark="" data-telephone="' . $address['telephone'] . '">Edit</a>';
            $formattedAddresses .= ' | ';
            $formattedAddresses .= '<a href="#" class="delete-address" data-address-id="' . $address['entity_id'] . '">Delete</a>';
            $formattedAddresses .= '</div><br>';
            $formattedAddresses .= '</div>';
            $firstAddress = false;
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($formattedAddresses);
    }
}
