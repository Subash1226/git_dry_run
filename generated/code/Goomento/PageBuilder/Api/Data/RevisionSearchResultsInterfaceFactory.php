<?php
namespace Goomento\PageBuilder\Api\Data;

/**
 * Factory class for @see \Goomento\PageBuilder\Api\Data\RevisionSearchResultsInterface
 */
class RevisionSearchResultsInterfaceFactory
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Goomento\\PageBuilder\\Api\\Data\\RevisionSearchResultsInterface')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Goomento\PageBuilder\Api\Data\RevisionSearchResultsInterface
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
