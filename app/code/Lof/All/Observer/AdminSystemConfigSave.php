<?php
namespace Lof\All\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

class AdminSystemConfigSave implements ObserverInterface
{
    protected $configWriter;
    protected $_cacheTypeList;
    protected $_cacheFrontendPool;
    protected $objectManager; // Add this property

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        ObjectManagerInterface $objectManager // Inject ObjectManagerInterface
    ) {
        $this->configWriter = $configWriter;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->objectManager = $objectManager; // Assign to class property
    }

    protected function flushCache()
    {
        $types = array('config', 'layout', 'block_html', 'full_page');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     *
     */
    public function flushConfigCache()
    {
        if (class_exists(System::class)) {
            $this->objectManager->get(System::class)->clean();
        } else {
            $this->objectManager->get(Config::class)
                ->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                    ['config_scopes']
                );
        }
    }

    /**
     * @param $type
     * @return bool
     */
    public function isCacheEnabled($type)
    {
        if (!isset($this->_cacheEnabled)) {
            $this->_cacheEnabled = $this->_state->isEnabled($type);
        }

        return $this->_cacheEnabled;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $configData = $observer->getConfigData();
        $request = $observer->getRequest();
        $section = $request->getParam("section");
        if (($section && $section == "loflicense") && (!$configData || ($configData && isset($configData['groups']) && !$configData['groups']))) {
            $groups = $request->getParam('groups');
            if ($groups && isset($groups['general']) && $groups['general']) {
                $modules = $groups['general']['fields'];
                if ($modules) {
                    foreach ($modules as $key => $item) {
                        $module_license_key = isset($item['value']) ? $item['value'] : '';
                        if ($module_license_key) {
                            $module_license_key = is_array($module_license_key) ? implode(",", $module_license_key) : $module_license_key;
                            $this->configWriter->save('loflicense/general/' . $key, $module_license_key, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                        }
                    }
                    $this->flushConfigCache();
                    $this->flushCache();
                }
            }
        }
    }
}
