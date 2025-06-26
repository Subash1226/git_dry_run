<?php

namespace Quick\Order\Block;

use Magento\Framework\View\Element\Template;

class Success extends Template
{
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
