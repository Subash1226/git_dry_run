<?php

namespace MedizinhubCore\Patient\Block\Adminhtml\Appointments\Form\Element;

use Magento\Framework\Data\Form\Element\File;

class Html extends File
{
    /**
     * Return element HTML markup.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->setData('multiple', 'multiple');
        $html = '<input type="file" name="' . $this->getName() . '" id="' . $this->getId() . '" ' . $this->serialize($this->getHtmlAttributes()) . ' multiple="multiple"/>';        return $html;
        return $html;
    }

    public function getHtmlAttributes()
    {
        return ['name', 'id', 'class', 'style', 'multiple'];
    }
}
