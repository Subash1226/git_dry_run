<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_PopupWindowMessage
 * @author    Dara Baig  (support@fmeextensions.com)
 * @copyright Copyright (c) 2018 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\PopupWindowMessage\Model;

class Popup extends \Magento\Rule\Model\AbstractModel
{
    protected $_combineFactory;
    protected $_coreResource;
    protected $_actionCollectionFactory;
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \FME\PopupWindowMessage\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        
        $this->_combineFactory = $combineFactory;
        $this->_coreResource = $coreResource;
        $this->_actionCollectionFactory = $actionCollectionFactory;
    }
    
    protected function _construct()
    {
        $this->_init('FME\PopupWindowMessage\Model\ResourceModel\Popup');
        $this->setIdFieldName('pwm_id');
    }

    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    public function getAvailableStatuses()
    {
        $availableOptions = ['0' => 'Disable',
                           '1' => 'Enable'];
        return $availableOptions;
    }

    public function getAvailableEvents()
    {
        $availableOptions = ['0' => 'Once The Page Is Loaded',
                           '1' => 'X seconds after the Page is Loaded',
                           '2' => 'Once the page is scrolled by X%',
                           '5' => 'Once cursor is moved outside the page'];
        return $availableOptions;
    }

    public function getAvailableEffects()
    {
        $availableOptions = ['mfp-fade-zoom' => 'Fade Zoom',
                             'mfp-fade-slide' => 'Fade Slide',
                             'mfp-newspaper' => 'Newspaper',
                             'mfp-move-horizontal' => 'Horizontal Move',
                             'mfp-move-from-top' => 'Move From Top',
                             'mfp-3d-unfold' => '3D Unfold',
                             'mfp-zoom-out' => 'Zoom Out'];
        return $availableOptions;
    }

    public function getAvailablePositions()
    {
        $availableOptions = ['top-left' => 'Top Left',
                           'top-center' => 'Top Center',
                           'top-right' => 'Top Right',
                           'middle-left' => 'Middle Left',
                           'middle-center' => 'Middle Center',
                           'middle-right' => 'Middle Right',
                           'bottom-left' => 'Bottom Left',
                           'bottom-center' => 'Bottom Center',
                           'bottom-right' => 'Bottom Right'];
        return $availableOptions;
    }

    public function getCMSPage()
    {
        $CMSTable = $this->_coreResource->getTableName('cms_page');
        $sqry = "select title as label, page_id as value from " . $CMSTable . " where is_active=1";
        $connection = $this->_coreResource->getConnection('core_read');
        $select = $connection->query($sqry);
        return $rows = $select->fetchAll();
    }
}
