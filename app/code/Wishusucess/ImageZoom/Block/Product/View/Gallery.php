<?php
namespace Wishusucess\ImageZoom\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Stdlib\ArrayUtils;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /** @var \Wishusucess\ImageZoom\Helper\Data */
    public $helper;

    public function __construct(
        \Wishusucess\ImageZoom\Helper\Data $helper,
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        array $data = [],
        ImagesConfigFactoryInterface $imagesConfigFactory = null,
        array $galleryImagesConfig = [],
        UrlBuilder $urlBuilder = null
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $data,
            $imagesConfigFactory,
            $galleryImagesConfig,
            $urlBuilder
        );
        $this->helper = $helper;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template): void
    {
        if ($this->helper->isEnabled()) {  // Changed from isEnable() to isEnabled()
            $this->_template = "Wishusucess_ImageZoom::zoom.phtml";
        } else {
            $this->_template = $template;
        }
    }
}
