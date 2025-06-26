<?php
namespace Wishusucess\ImageZoom\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'wishusucess_imagezoom/general/enable';
    const XML_PATH_MINI_HIDDEN = 'wishusucess_imagezoom/general/mini_hidden';
    const XML_PATH_MAGNIFICATION_DEGREE = 'wishusucess_imagezoom/general/magnification_degree';
    const XML_PATH_CONTAINER_WIDTH = 'wishusucess_imagezoom/zoom_container/width';
    const XML_PATH_CONTAINER_HEIGHT = 'wishusucess_imagezoom/zoom_container/height';
    const XML_PATH_CONTAINER_TOP = 'wishusucess_imagezoom/zoom_container/position_top';
    const XML_PATH_CONTAINER_RIGHT = 'wishusucess_imagezoom/zoom_container/position_right';
    const XML_PATH_MAGNIFICATION_WIDTH = 'wishusucess_imagezoom/magnification/width';
    const XML_PATH_MAGNIFICATION_HEIGHT = 'wishusucess_imagezoom/magnification/height';

    /**
     * Check if module is enabled
     *
     * @param mixed $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Alias for isEnabled to maintain backward compatibility
     *
     * @param mixed $store
     * @return bool
     */
    public function isEnable($store = null)
    {
        return $this->isEnabled($store);
    }

    public function getMiniWithToHiddenZoom($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MINI_HIDDEN,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getMagnificationDegree($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAGNIFICATION_DEGREE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getZoomContainerWidth($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTAINER_WIDTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getZoomContainerHeight($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTAINER_HEIGHT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getZoomContainerPositionTop($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTAINER_TOP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getZoomContainerPositionRight($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTAINER_RIGHT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getZoomMagnificationWidth($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAGNIFICATION_WIDTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getZoomMagnificationHeight($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAGNIFICATION_HEIGHT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
