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
namespace Apptha\Marketplace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session as CustomerSession;
use Apptha\Marketplace\Model\Product\Gallery\Video\Processor as VideoGalleryProcessor;
use Apptha\Marketplace\Model\Subscriptionprofiles;
use Apptha\Marketplace\Model\Bulkupload;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as CatalogProduct;

class Marketplace extends AbstractHelper
{
    protected $messageManager;
    protected $videoGalleryProcessor;
    protected $directoryList;
    protected $file;
    protected $customerSession;
    protected $subscriptionProfiles;
    protected $bulkUpload;
    protected $productRepository;
    protected $catalogProduct;
    protected $dateTime;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        VideoGalleryProcessor $videoGalleryProcessor,
        DirectoryList $directoryList,
        File $file,
        CustomerSession $customerSession,
        Subscriptionprofiles $subscriptionProfiles,
        Bulkupload $bulkUpload,
        ProductRepositoryInterface $productRepository,
        CatalogProduct $catalogProduct,
        DateTime $dateTime
    ) {
        $this->messageManager = $messageManager;
        $this->videoGalleryProcessor = $videoGalleryProcessor;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->customerSession = $customerSession;
        $this->subscriptionProfiles = $subscriptionProfiles;
        $this->bulkUpload = $bulkUpload;
        $this->productRepository = $productRepository;
        $this->catalogProduct = $catalogProduct;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    public function saveDownLoadLink($linkModel)
    {
        $linkModel->save();
        return true;
    }

    public function isSellerSubscriptionEnabled($productData)
    {
        $isSellerSubscriptionEnabled = $this->helper('Apptha_Marketplace/Data')->isSellerSubscriptionEnabled();
        $customerId = $this->customerSession->getId();
        if ($isSellerSubscriptionEnabled == 1) {
            $sellerSubscribedPlan = $this->subscriptionProfiles->getCollection();
            $sellerSubscribedPlan->addFieldToFilter('seller_id', $customerId);
            $sellerSubscribedPlan->addFieldToFilter('status', 1);
            $sellerSubscribedPlan->addFieldtoFilter('ended_at', [
                ['gteq' => $this->dateTime->gmtDate()],
                ['ended_at', 'null' => '']
            ]);
            if ($sellerSubscribedPlan->count()) {
                $maximumCount = '';
                foreach ($sellerSubscribedPlan as $subscriptionProfile) {
                    $maximumCount = $subscriptionProfile->getMaxProductCount();
                    break;
                }
                $productDataTotalCount = 0;
                $sellerProduct = $this->catalogProduct->getCollection()->addFieldToFilter('seller_id', $customerId);
                $sellerIdForProducts = $sellerProduct->getAllIds();
                $productDataTotalCount = $this->bulkUpload->getProductTotalCount($productData);
                $sellerProductCount = count($sellerIdForProducts) + $productDataTotalCount;
                $this->subscriptionLimit($maximumCount, $sellerProductCount);
                return;
            } else {
                $this->messageManager->addNotice(__('You have not subscribed any plan yet. Kindly subscribe for adding product(s).'));
                $this->_redirect('marketplace/seller/subscriptionplans');
                return;
            }
        }
    }

    public function addVideo($product, $postValue)
    {
        $product->setStoreId(0);
        if ($postValue['product_video']) {
            parse_str(parse_url($postValue['product_video'], PHP_URL_QUERY), $myArrayOfVars);
            $videoId = $myArrayOfVars['v'];

            $videoData = [
                'video_id' => $videoId,
                'video_title' => $postValue['video_title'],
                'video_description' => $postValue['video_description'],
                'thumbnail' => $postValue['video_thumbnailurl'],
                'video_provider' => 'youtube',
                'video_metadata' => null,
                'video_url' => $postValue['product_video'],
                'media_type' => \Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
            ];

            $tmpDir = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'Productvideo' . DIRECTORY_SEPARATOR . $videoId . DIRECTORY_SEPARATOR;
            $this->file->mkdir($tmpDir);
            $newFileName = $tmpDir . basename($videoData['thumbnail']);
            $this->file->read($videoData['thumbnail'], $newFileName);

            $videoData['file'] = $videoData['video_id'] . '_hqdefault.jpg';
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            if (!empty($existingMediaGalleryEntries)) {
                foreach ($existingMediaGalleryEntries as $key => $entry) {
                    if ($entry['media_type'] == 'external-video') {
                        unset($existingMediaGalleryEntries[$key]);
                    }
                }
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                $this->productRepository->save($product);
            }

            if ($product->hasGalleryAttribute()) {
                $this->videoGalleryProcessor->addVideo(
                    $product,
                    $videoData,
                    ['image', 'small_image', 'thumbnail'],
                    false,
                    true
                );
                $product->save();
            }
        } else {
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            if (!empty($existingMediaGalleryEntries)) {
                foreach ($existingMediaGalleryEntries as $key => $entry) {
                    if ($entry['media_type'] == 'external-video') {
                        unset($existingMediaGalleryEntries[$key]);
                    }
                }
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                $this->productRepository->save($product);
            }
        }
        return $product;
    }
}
