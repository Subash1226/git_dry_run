<?php
namespace Mageplaza\BannerSlider\Model;

use Mageplaza\BannerSlider\Api\BannerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BannerSlider\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\UrlInterface;

class BannerApi implements BannerInterface 
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * BannerApi constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $bannerCollectionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        CollectionFactory $bannerCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
    }   

    /**
     * Get banners using collection factory
     *
     * @return array
     */
    public function getBannersCollection()
    {
        $collection = $this->bannerCollectionFactory->create();
        $collection->addFieldToFilter('status', 1)
            ->addFieldToFilter('category', 1)
            ->setOrder('title', 'ASC');
            
        $mediaBaseUrl = $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            
        $banners = [];
        foreach ($collection as $banner) {
            $banners[] = [
                'name' => $banner->getName(),
                'image_url' => $mediaBaseUrl . 'mageplaza/bannerslider/banner/image/' . $banner->getImage(),
                'sort_order' => $banner->getTitle()
            ];
        }
        
        return $banners;
    }

    /**
     * Get all active banners (combines both methods)
     *
     * @return array
     */
    public function getBanner()
    {
        // You can choose which method to use or combine them
        return $this->getBannersCollection();
    }
}