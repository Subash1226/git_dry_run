<?php
namespace MedizinhubCore\Patient\Ui\Component\Appointments\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class PractitionerName extends Column
{
   protected $resource;

   protected $practitioners = null;

   public function __construct(
       ContextInterface $context,
       UiComponentFactory $uiComponentFactory,
       array $components = [],
       array $data = []
   ) {
       parent::__construct($context, $uiComponentFactory, $components, $data);
       $this->resource = ObjectManager::getInstance()->get(ResourceConnection::class);
   }

   /**
    * Get practitioners from database
    *
    * @return array
    */
   protected function getPractitioners()
   {
       if ($this->practitioners === null) {
           $connection = $this->resource->getConnection();
           $practitionersQuery = "SELECT id, name FROM practitioners WHERE status = 1";
           $this->practitioners = $connection->fetchPairs($practitionersQuery);
       }
       return $this->practitioners;
   }

   public function prepareDataSource(array $dataSource)
   {
       if (isset($dataSource['data']['items'])) {
           $practitioners = $this->getPractitioners();

           foreach ($dataSource['data']['items'] as &$item) {
               if (isset($item['practitioner_id']) && isset($practitioners[$item['practitioner_id']])) {
                   $item['practitioner_id'] = $practitioners[$item['practitioner_id']];
               }
           }
       }

       return $dataSource;
   }
}
