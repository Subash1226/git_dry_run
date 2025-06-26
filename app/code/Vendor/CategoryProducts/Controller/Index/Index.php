<?php
namespace Vendor\CategoryProducts\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;

class Index extends Action
{
    protected $resultPageFactory;
    protected $resultForwardFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('id');
        if (!$categoryId) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        $resultPage = $this->resultPageFactory->create();
        // Add breadcrumbs
        if ($resultPage->getConfig()->getTitle()->getShortHeading()) {
            $this->_addBreadcrumbs($resultPage);
        }

        return $resultPage;
    }

    protected function _addBreadcrumbs($resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_url->getUrl('')
                ]
            );
            $breadcrumbs->addCrumb(
                'category',
                [
                    'label' => __('Category'),
                    'title' => __('Category')
                ]
            );
        }
    }
}
