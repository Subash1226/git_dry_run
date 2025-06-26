<?php
namespace Snowdog\CustomDescription\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template;

class ExpiredBatches extends Template
{
    protected $_template = 'Snowdog_CustomDescription::product/edit/expired_batches.phtml';

    public function getExpiredBatches()
    {
        // Implement your logic to fetch expired batches
        return [];
    }
}