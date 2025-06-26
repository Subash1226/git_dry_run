<?php
namespace Sp\Orderattachment\Api\Data;

/**
 * Factory class for @see \Sp\Orderattachment\Api\Data\AttachmentSearchResultsInterface
 */
class AttachmentSearchResultsInterfaceFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Sp\\Orderattachment\\Api\\Data\\AttachmentSearchResultsInterface')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Sp\Orderattachment\Api\Data\AttachmentSearchResultsInterface
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
