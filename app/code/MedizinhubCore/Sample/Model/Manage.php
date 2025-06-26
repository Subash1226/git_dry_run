<?php

namespace MedizinhubCore\Sample\Model;
/**
 * Class Reviews
 * @package MedizinhubCore\Sample\Model
 */
class Manage extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'customer_labtest_manage';
    /**
     * @var string
     */
    protected $_cacheTag = 'customer_labtest_manage';
    /**
     * @var string
     */
    protected $_eventPrefix = 'customer_labtest_manage';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('MedizinhubCore\Sample\Model\ResourceModel\Manage');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
