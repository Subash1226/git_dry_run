<?php
namespace MedizinhubCore\Offers\Plugin\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Save;
use Magento\SalesRule\Model\RuleFactory;

class SavePlugin
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param RuleFactory $ruleFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        RuleFactory $ruleFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->ruleFactory = $ruleFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Handle coupon image upload before saving the rule
     *
     * @param Save $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(Save $subject, \Closure $proceed)
    {
        $data = $subject->getRequest()->getPostValue();
        
        if ($data) {
            try {
                if (isset($_FILES['coupon_image']) && !empty($_FILES['coupon_image']['name'])) {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'coupon_image']);
                    
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);

                    $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    $destinationPath = $mediaDirectory->getAbsolutePath('salesrule/images');

                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $result = $uploader->save($destinationPath);

                    if ($result['file']) {
                        $data['coupon_image'] = 'salesrule/images' . $result['file'];
                    }
                }
            } catch (\Exception $e) {
                $subject->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }

        $subject->getRequest()->setPostValue($data);

        return $proceed();
    }
}