<?php
namespace SalesOrder\UserLog\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use SalesOrder\UserLog\Model\ResourceModel\OrderViewLog\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ViewLog extends Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'SalesOrder_UserLog::order/view/view_log.phtml';
    protected $collectionFactory;
    protected $registry;
    protected $timezone;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Registry $registry,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    public function getViewLogs()
    {
        $orderId = $this->getOrder()->getId();
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->setOrder('viewed_at', 'DESC');
        return $collection;
    }

    public function getFormattedTime($time)
    {
    return $this->timezone->formatDateTime(
        new \DateTime($time),
        \IntlDateFormatter::MEDIUM,
        \IntlDateFormatter::MEDIUM
    );
    }

    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }
    
    public function getActivityTypeLabel($activityType)
    {
        $labels = [
            'reorder_completed' => __('Reorder Completed'),
            'shipment_completed' => __('Shipment Completed'),
            'invoice_completed' => __('Invoice Completed'),
            'order_held' => __('Order Held'),
            'order_unheld' => __('Order Unheld'),
            'email_sent' => __('Email Sent'),
            'order_cancelled' => __('Order Cancelled'),
            'comment_added' => __('Comment Added')
        ];
        return isset($labels[$activityType]) ? $labels[$activityType] : __('Other Activity');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('User Activity Logs');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('User Activity Logs');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}