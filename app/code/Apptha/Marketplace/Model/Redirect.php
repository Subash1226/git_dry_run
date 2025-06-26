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

namespace Apptha\Marketplace\Model;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Url\DecoderInterface;

/**
 * This class contains redirect URL functions.
 */
class Redirect
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var CustomerSession
     */
    protected $session;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     * @param DecoderInterface $urlDecoder
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        RequestInterface $request,
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        DecoderInterface $urlDecoder,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->request = $request;
        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->urlDecoder = $urlDecoder;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Retrieve redirect
     *
     * @return ResultRedirect
     */
    public function getRedirect()
    {
        $this->updateLastCustomerId();
        $this->prepareRedirectUrl();

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->session->getBeforeAuthUrl(true));
        return $resultRedirect;
    }

    /**
     * Function to get Seller Redirect
     *
     * @return ResultRedirect
     */
    public function getSellerRedirect()
    {
        $this->prepareSellerRedirectUrl();

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->session->getBeforeAuthUrl(true));
        return $resultRedirect;
    }

    /**
     * Update last customer ID, if required
     *
     * @return void
     */
    protected function updateLastCustomerId()
    {
        $lastCustomerId = $this->session->getLastCustomerId();
        if (isset($lastCustomerId) && $this->session->isLoggedIn() && $lastCustomerId != $this->session->getId()) {
            $this->session->unsBeforeAuthUrl()->setLastCustomerId($this->session->getId());
        }
    }

    /**
     * Prepare redirect URL
     *
     * @return void
     */
    protected function prepareRedirectUrl()
    {
        $objectModelManager = \Magento\Framework\App\ObjectManager::getInstance();
        $baseUrl = $objectModelManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();

        $url = $this->session->getBeforeAuthUrl();
        if (!$url) {
            $url = $baseUrl;
        }

        switch ($url) {
            case $baseUrl:
                if ($this->session->isLoggedIn()) {
                    $this->processLoggedCustomer();
                } else {
                    $this->applyRedirect($this->url->getUrl('customer/account/login'));
                }
                break;

            case $this->url->getUrl('customer/account/logout'):
                $this->applyRedirect($this->url->getUrl('customer/account'));
                break;

            default:
                if (!$this->session->getAfterAuthUrl()) {
                    $this->session->setAfterAuthUrl($this->session->getBeforeAuthUrl());
                }
                if ($this->session->isLoggedIn()) {
                    $this->applyRedirect($this->session->getAfterAuthUrl(true));
                }
                break;
        }
    }

    /**
     * Prepare seller redirect URL
     *
     * @return void
     */
    protected function prepareSellerRedirectUrl()
    {
        $this->applyRedirect($this->url->getUrl('customer/account/login'));
    }

    /**
     * Prepare redirect URL for logged in customer
     *
     * Redirect customer to the last page visited after logging in.
     *
     * @return void
     */
    protected function processLoggedCustomer()
    {
        // Set default redirect URL for logged in customer
        $this->applyRedirect($this->url->getUrl('customer/account'));

        if (!$this->scopeConfig->isSetFlag('customer/startup/redirect_dashboard', ScopeInterface::SCOPE_STORE)) {
            $referer = $this->request->getParam('referer');
            if ($referer) {
                $referer = $this->urlDecoder->decode($referer);
                if ($this->url->isOwnOriginUrl($referer)) {
                    $this->applyRedirect($referer);
                }
            }
        } elseif ($this->session->getAfterAuthUrl()) {
            $this->applyRedirect($this->session->getAfterAuthUrl(true));
        }
    }

    /**
     * Apply redirect URL
     *
     * @param string $url
     * @return void
     */
    private function applyRedirect($url)
    {
        $this->session->setBeforeAuthUrl($url);
    }
}
