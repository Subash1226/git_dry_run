<?php
namespace Lof\All\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Dir;

class CheckLicense implements ObserverInterface
{
    /**
     * @var \Lof\All\Model\License
     */
    protected $_license;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;  // Declare the property here

    /**
     * @var \Lof\All\Helper\Data
     */
    protected $licenseHelper;  // Declare the property for licenseHelper here

    /**
     * @param \Lof\All\Model\License                               $licnese
     * @param \Magento\Framework\Module\Dir\Reader                 $moduleReader
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Lof\All\Helper\Data                                  $licenseHelper
     */
    public function __construct(
        \Lof\All\Model\License $licnese,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Lof\All\Helper\Data $licenseHelper
    ) {
        $this->_license       = $licnese;
        $this->_moduleReader  = $moduleReader;
        $this->messageManager = $messageManager;
        $this->_storeManager  = $storeManager;
        $this->_remoteAddress = $remoteAddress;
        $this->licenseHelper  = $licenseHelper;  // Assign the value to the declared property
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ip         = $this->_remoteAddress->getRemoteAddress();
        $obj        = $observer->getObj();
        $moduleName = $observer->getEx();
        $license    = $this->licenseHelper->getLicense($moduleName);

        if (($license && is_bool($license)) || ($license && $license->getStatus())) {
            $obj->setData('is_valid', 1);
        } else {
            $obj->setData('is_valid', 0);
            if ($ip == '127.0.0.1') {
                $obj->setData('local_valid', 1);
            } else {
                $obj->setData('local_valid', 0);
            }
        }
    }
}
