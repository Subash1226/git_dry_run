<?php

namespace MedizinhubCore\Patient\Model;
/**
 * Class Reviews
 * @package MedizinhubCore\Patient\Model
 */
class Manage extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = 'medizinhubcore_patient_manage';
    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    /**
     * @var string
     */
    protected $_eventPrefix = 'medizinhubcore_patient_manage';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('MedizinhubCore\Patient\Model\ResourceModel\Patient');
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
