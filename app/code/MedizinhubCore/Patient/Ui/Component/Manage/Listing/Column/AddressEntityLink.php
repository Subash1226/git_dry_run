<?php

namespace MedizinhubCore\Patient\Ui\Component\Manage\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class AddressEntityLink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
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
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = sprintf(
                    '<button class="action view-address" data-address="%s" id="address-details-%s" onclick="viewAddress(%s);">View Address</button>',
                    htmlspecialchars(json_encode([
                        'house_no' => $item['house_no'],
                        'street' => $item['street'],
                        'area' => $item['area'],
                        'city' => $item['city'],
                        'postcode' => $item['postcode']
                    ])),
                    $item['id'],
                    $item['id']
                );
            }
        }
        return $dataSource;
    }
}
