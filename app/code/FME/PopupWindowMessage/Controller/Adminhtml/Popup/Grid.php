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

namespace FME\PopupWindowMessage\Controller\Adminhtml\Popup;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Category
{

    protected $resultRawFactory;
    protected $layoutFactory;
    const ADMIN_RESOURCE = 'FME_PopupWindowMessage::manage_popup';
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $category = $this->_initCategory(true);
        if (!$category) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('popupwindowmessage/*/', ['_current' => true, 'id' => null]);
        }

        $resultRaw = $this->resultRawFactory->create();
    }
}
