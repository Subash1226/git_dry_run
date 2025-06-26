<?php
namespace MedizinhubCore\Lab\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
class Lab extends Template
{
    protected $request;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $data);
    }

}
