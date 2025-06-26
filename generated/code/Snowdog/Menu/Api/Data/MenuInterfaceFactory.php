<?php
namespace Snowdog\Menu\Api\Data;

/**
 * Factory class for @see \Snowdog\Menu\Api\Data\MenuInterface
 */
class MenuInterfaceFactory
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Snowdog\\Menu\\Api\\Data\\MenuInterface')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Snowdog\Menu\Api\Data\MenuInterface
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
