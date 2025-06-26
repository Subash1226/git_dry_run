<?php
namespace Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * Interceptor class for @see \Magento\MediaStorage\Helper\File\Storage\Database
 */
class Interceptor extends \Magento\MediaStorage\Helper\File\Storage\Database implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $dbStorageFactory, \Magento\MediaStorage\Model\File\Storage\File $fileStorage, \Magento\Framework\Filesystem $filesystem)
    {
        $this->___init();
        parent::__construct($context, $dbStorageFactory, $fileStorage, $filesystem);
    }

    /**
     * {@inheritdoc}
     */
    public function checkDbUsage()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'checkDbUsage');
        return $pluginInfo ? $this->___callPlugins('checkDbUsage', func_get_args(), $pluginInfo) : parent::checkDbUsage();
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageDatabaseModel()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getStorageDatabaseModel');
        return $pluginInfo ? $this->___callPlugins('getStorageDatabaseModel', func_get_args(), $pluginInfo) : parent::getStorageDatabaseModel();
    }

    /**
     * {@inheritdoc}
     */
    public function saveFileToFilesystem($filename)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'saveFileToFilesystem');
        return $pluginInfo ? $this->___callPlugins('saveFileToFilesystem', func_get_args(), $pluginInfo) : parent::saveFileToFilesystem($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaRelativePath($fullPath)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getMediaRelativePath');
        return $pluginInfo ? $this->___callPlugins('getMediaRelativePath', func_get_args(), $pluginInfo) : parent::getMediaRelativePath($fullPath);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFolder($folderName)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'deleteFolder');
        return $pluginInfo ? $this->___callPlugins('deleteFolder', func_get_args(), $pluginInfo) : parent::deleteFolder($folderName);
    }

    /**
     * {@inheritdoc}
     */
    public function saveUploadedFile($result)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'saveUploadedFile');
        return $pluginInfo ? $this->___callPlugins('saveUploadedFile', func_get_args(), $pluginInfo) : parent::saveUploadedFile($result);
    }
}
