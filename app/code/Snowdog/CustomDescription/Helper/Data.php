<?php

namespace Snowdog\CustomDescription\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Data
 * @package Snowdog\CustomDescription\Helper
 */
class Data extends AbstractHelper
{
    private const IMAGES_UPLOAD = 'snowdog/customdescription/images/';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param Context $context
     * @param DateTime $dateTime
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Context $context,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->dateTime = $dateTime;
        $this->ensureUploadDirectoryExists();
    }

    /**
     * Ensures the upload directory exists; creates it if necessary.
     */
    private function ensureUploadDirectoryExists(): void
    {
        $uploadPath = self::IMAGES_UPLOAD;
        if (!$this->mediaDirectory->isDirectory($uploadPath)) {
            $this->mediaDirectory->create($uploadPath, 0777, true);
        }
    }

    /**
     * Get the URL of an image.
     *
     * @param string $imageName
     * @return string
     */
    public function getImageUrl(string $imageName): string
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::IMAGES_UPLOAD
            . $imageName;
    }

    /**
     * Extract the image name from its path.
     *
     * @param string $imagePath
     * @return string
     */
    public function getImageNameFromPath(string $imagePath): string
    {
        return basename($imagePath);
    }

    /**
     * Get the size of an image file.
     *
     * @param string $image
     * @return int
     * @throws LocalizedException
     */
    public function getImageSize(string $image): int
    {
        $path = self::IMAGES_UPLOAD . $image;
        if (!$this->mediaDirectory->isFile($path)) {
            throw new LocalizedException(__('Image file does not exist: %1', $path));
        }
        return $this->mediaDirectory->stat($path)['size'];
    }

    /**
     * Check if an image file exists.
     *
     * @param string $image
     * @return bool
     */
    public function isExistingImage(string $image): bool
    {
        return $this->mediaDirectory->isFile(self::IMAGES_UPLOAD . $image);
    }

    /**
     * Get the full path to an image file.
     *
     * @param string $image
     * @return string
     */
    public function getImageFullPath(string $image): string
    {
        return $this->mediaDirectory->getAbsolutePath(self::IMAGES_UPLOAD . $image);
    }

    /**
     * Format a date to a specific format.
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        return $this->dateTime->formatDate($date, $format);
    }

    /**
     * Get current date in the specified format.
     *
     * @param string $format
     * @return string
     */
    public function getCurrentDate(string $format = 'Y-m-d'): string
    {
        return $this->dateTime->date()->format($format);
    }

    /**
     * Validate if the given price is a valid decimal value.
     *
     * @param string $price
     * @return bool
     */
    public function validatePrice(string $price): bool
    {
        return is_numeric($price) && $price >= 0;
    }
}
