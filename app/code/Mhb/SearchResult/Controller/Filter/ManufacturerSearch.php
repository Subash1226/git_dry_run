<?php
namespace Mhb\SearchResult\Controller\Filter;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mhb\SearchResult\Block\Filter;

class ManufacturerSearch extends Action
{
    protected $block;

    public function __construct(Context $context, Filter $block)
    {
        $this->block = $block;
        parent::__construct($context);
    }

    public function execute()
    {
        $search = $this->getRequest()->getParam('search');
        $manufacturers = $this->block->getManufacturers(10, $search);
        $html = '';
        
        foreach ($manufacturers as $manufacturer) {
            $html .= '<li class="cusrp-filter-item">';
            $html .= '<div class="cusrp-form-check">';
            $html .= '<input type="checkbox" class="cusrp-filter-checkbox" data-filter="manufacturer" data-value="' . $manufacturer['value'] . '" id="man_' . $manufacturer['value'] . '">';
            $html .= '<label class="cusrp-filter-label" for="man_' . $manufacturer['value'] . '">' . $manufacturer['label'] . '</label>';
            $html .= '</div>';
            $html .= '</li>';
        }
        
        if (empty($manufacturers)) {
            $html = '<li class="cusrp-filter-item-empty">No manufacturers found</li>';
        }
        
        $this->getResponse()->setBody($html);
    }
}