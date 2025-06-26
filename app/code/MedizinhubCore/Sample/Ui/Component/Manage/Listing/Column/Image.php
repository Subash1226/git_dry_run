<?php

namespace MedizinhubCore\Sample\Ui\Component\Manage\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;

class Image extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    protected $assetRepo;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Repository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['products_image'])) {
                    $imageUrl = $this->assetRepo->getUrl('MedizinhubCore_Sample::images/'.$item['products_image']);
                    $item[$this->getData('name')] = '<img src="' . $imageUrl . '" width="50" height="50" />';
                }
            }
        }

        return $dataSource;
    }
}
