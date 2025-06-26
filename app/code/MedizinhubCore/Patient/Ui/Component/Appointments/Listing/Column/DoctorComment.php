<?php

namespace MedizinhubCore\Patient\Ui\Component\Appointments\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

class DoctorComment extends Column
{

    protected $urlBuilder;
    protected $resourceConnection;
    protected $logger;
    protected $_storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                
                // Add a log to verify the appointment_status value
                $this->logger->info('Appointment Status: ' . $item['appointment_status']); 
                
                $appointmentStatus = trim($item['appointment_status']); // Trim spaces if any
                $doctorCommentData = $this->getDoctorCommentData($item['appointment_id']);

                // Make sure status is "Completed" exactly as it's in the DB
                if ($appointmentStatus === 'Completed') {
                    if ($doctorCommentData) {
                        $item[$this->getData('name')] = sprintf(
                            '<button class="action view-details" data-details=\'%s\' data-image-url="%s" id="details-%s" onclick="openDetailsModal(%d);">Practitioner Comment</button>',
                            htmlspecialchars(json_encode([
                                'doctor_prescription' => $doctorCommentData['doctor_prescription'],
                                'comments' => $doctorCommentData['comment']
                            ])),
                            $doctorCommentData['doctor_prescription'],
                            $item['appointment_id'],
                            $item['appointment_id']
                        );
                    } else {
                        $item[$this->getData('name')] = sprintf(
                            '<button class="action view-details" data-details=\'%s\' data-image-url="%s" id="details-%s" onclick="openDetailsModal(%d);">Add Comment</button>',
                            htmlspecialchars(json_encode([
                                'doctor_prescription' => null,
                                'comments' => ''
                            ])),
                            '',
                            $item['appointment_id'],
                            $item['appointment_id']
                        );
                    }
                } else {
                    $item[$this->getData('name')] = '<button class="action view-details" disabled>No Comment Available</button>';
                }
            }
        }
        return $dataSource;
    }

    protected function getDoctorCommentData($appointmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from('doctor_comment')
            ->where('appointment_id = ?', $appointmentId)
            ->limit(1);

        $result = $connection->fetchRow($select);
        if ($result && isset($result['doctor_prescription'])) {
            $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $result['doctor_prescription'] = $mediaDirectory . $result['doctor_prescription'];
        }
        return $result ?: null;
    }
}
