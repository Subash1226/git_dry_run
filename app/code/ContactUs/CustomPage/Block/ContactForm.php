<?php
namespace ContactUs\CustomPage\Block;

use Magento\Framework\View\Element\Template;

class ContactForm extends Template
{
    public function getFormAction()
    {
        return $this->getUrl('contactus/index/post');
    }
}