<?php

namespace MedizinhubCore\Patient\Ui\Component\Appointments\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;

class PrescriptionImage extends Column
{
    protected $urlBuilder;
    protected $_storeManager;
    protected $_assetRepo;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                $filesContainer = '';
                $files = isset($item['doctor_prescription']) ? explode(',', $item['doctor_prescription']) : [];

                foreach ($files as $file) {
                    $fileUrl = $mediaDirectory . trim($file);
                    $isPdf = strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';

                    if ($isPdf) {
                        $pdfIconUrl = $this->_assetRepo->getUrl('MedizinhubCore_Patient::images/pdf.png');
                        $filesContainer .= "<img src='" . $pdfIconUrl . "' alt='PDF Icon' class='thumbnail-pdf' data-pdf-url='" . $fileUrl . "' style='cursor: pointer;' /><br/>";
                    } else {
                        $filesContainer .= "<img src='" . $fileUrl . "' width='50px' height='50px' class='thumbnail-image' data-image-url='" . $fileUrl . "' style='cursor: pointer;' /><br/>";
                    }
                }
                $item[$fieldName] = $filesContainer;
            }
        }

        return $dataSource;
    }
}
