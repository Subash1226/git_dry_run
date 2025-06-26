<?php

/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * Apptha does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Marketplace
 * @version     1.2
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2017 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */

 namespace Apptha\Marketplace\Controller\Seller;

 use Magento\Customer\Model\Account\Redirect as AccountRedirect;
 use Magento\Framework\App\Action\Context;
 use Magento\Customer\Model\Session;
 use Magento\Customer\Api\AccountManagementInterface;
 use Magento\Customer\Model\Url as CustomerUrl;
 use Magento\Framework\Exception\EmailNotConfirmedException;
 use Magento\Framework\Exception\AuthenticationException;
 use Magento\Framework\Data\Form\FormKey\Validator;
 use Magento\Framework\Exception\LocalizedException;
 use Magento\Framework\Exception\State\UserLockedException;
 use Magento\Framework\App\Config\ScopeConfigInterface;
 use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
 use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

 class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
 {
     /** @var AccountManagementInterface */
     protected $customerAccountManagement;

     /** @var Validator */
     protected $formKeyValidator;

     /** @var AccountRedirect */
     protected $accountRedirect;

     /** @var Session */
     protected $session;

     /** @var ScopeConfigInterface */
     protected $scopeConfig;

     /** @var PhpCookieManager */
     protected $cookieMetadataManager;

     /** @var CookieMetadataFactory */
     protected $cookieMetadataFactory;

     /** @var CustomerUrl */
     protected $customerUrl;

     /**
      * @param Context $context
      * @param Session $customerSession
      * @param AccountManagementInterface $customerAccountManagement
      * @param CustomerUrl $customerHelperData
      * @param Validator $formKeyValidator
      * @param AccountRedirect $accountRedirect
      * @param ScopeConfigInterface $scopeConfig
      * @param PhpCookieManager $cookieMetadataManager
      * @param CookieMetadataFactory $cookieMetadataFactory
      */
     public function __construct(
         Context $context,
         Session $customerSession,
         AccountManagementInterface $customerAccountManagement,
         CustomerUrl $customerHelperData,
         Validator $formKeyValidator,
         AccountRedirect $accountRedirect,
         ScopeConfigInterface $scopeConfig,
         PhpCookieManager $cookieMetadataManager,
         CookieMetadataFactory $cookieMetadataFactory
     ) {
         $this->session = $customerSession;
         $this->customerAccountManagement = $customerAccountManagement;
         $this->customerUrl = $customerHelperData;
         $this->formKeyValidator = $formKeyValidator;
         $this->accountRedirect = $accountRedirect;
         $this->scopeConfig = $scopeConfig;
         $this->cookieMetadataManager = $cookieMetadataManager;
         $this->cookieMetadataFactory = $cookieMetadataFactory;
         parent::__construct(
             $context,
             $customerSession,
             $customerAccountManagement,
             $customerHelperData,
             $formKeyValidator,
             $accountRedirect
         );
     }
    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                    if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
                        $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));

                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (UserLockedException $e) {
                    $message = __(
                        'The account is locked. Please wait and try again or contact %1.',
                        $this->scopeConfig->getValue('contact/email/recipient_email')
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }
        if (isset($customer) && $customer->getGroupId() == 4) {
            $redirectUrl = $this->resultRedirectFactory->create()->setPath('marketplace/seller/dashboard/', ['_current' => true]);
        } else {
            $redirectUrl = $this->accountRedirect->getRedirect();
        }

        return $redirectUrl;
    }
}
