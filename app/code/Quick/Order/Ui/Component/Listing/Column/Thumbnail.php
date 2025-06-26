<?php
namespace Quick\Order\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $_storeManager;
    protected $_assetRepo;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $assetRepo;
    }

    public function prepareDataSource(array $dataSource)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $pdfIconUrl = $this->_assetRepo->getUrl('Quick_Order::images/pdf.png');

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                $filesContainer = '';
                $files = isset($item['image']) ? explode(',', $item['image']) : [];

                foreach ($files as $file) {
                    $fileUrl = $mediaDirectory . trim($file);
                    $isPdf = strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';

                    if ($isPdf) {
                        $filesContainer .= "<img src='" . $pdfIconUrl . "' alt='PDF Icon' class='thumbnail-pdf' data-pdf-url='" . $fileUrl . "' style='cursor: pointer;' /><br/>";
                    } else {
                        $filesContainer .= "<a href='" . $fileUrl . "' target='_blank'><img src='" . $fileUrl . "' width='50px' height='50px' class='thumbnail-image' data-image-url='" . $fileUrl . "' /></a><br/>";
                    }
                }
                $item[$fieldName] = $filesContainer;
            }
        }

        return $dataSource;
    }
}
