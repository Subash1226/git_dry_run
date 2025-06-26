<?php
namespace MedizinhubCore\Patient\Block;

use Magento\Framework\View\Element\Template;
use MedizinhubCore\Patient\Model\ResourceModel\Patient\CollectionFactory as PatientCollectionFactory;
use MedizinhubCore\Patient\Model\ResourceModel\PatientAppointment\CollectionFactory as AppointmentCollectionFactory;
use MedizinhubCore\Patient\Model\ResourceModel\DoctorComment\CollectionFactory as DoctorCommentCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;

class GetAppointmentDate extends Template
{
    protected $patientCollectionFactory;
    protected $appointmentCollectionFactory;
    protected $doctorCommentCollectionFactory;
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        PatientCollectionFactory $patientCollectionFactory,
        AppointmentCollectionFactory $appointmentCollectionFactory,
        DoctorCommentCollectionFactory $doctorCommentCollectionFactory,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->patientCollectionFactory = $patientCollectionFactory;
        $this->appointmentCollectionFactory = $appointmentCollectionFactory;
        $this->doctorCommentCollectionFactory = $doctorCommentCollectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getAllPatientsAppointments()
    {
        // Get current customer ID
        $customerId = $this->customerSession->getCustomerId();

        // Fetch all patients data for the current customer
        $patientCollection = $this->patientCollectionFactory->create();
        $patientCollection->addFieldToFilter('customer_id', $customerId);

        $appointmentData = [];

        // Loop through each patient and get their appointments
        foreach ($patientCollection as $patient) {
            $patientId = $patient->getId();

            // Fetch all appointment data for the current patient
            $appointmentCollection = $this->appointmentCollectionFactory->create();
            $appointmentCollection->addFieldToFilter('patient_id', $patientId);

            // If the patient has appointments, add to the result array
            if ($appointmentCollection->getSize()) {
                foreach ($appointmentCollection as $appointment) {
                    $appointmentId = $appointment->getId();

                    // Fetch all comments for the current appointment
                    $doctorCommentCollection = $this->doctorCommentCollectionFactory->create();
                    $doctorCommentCollection->addFieldToFilter('appointment_id', $appointmentId);

                    $appointmentData[] = [
                        'patient' => $patient->getData(),  // Fetch all patient details
                        'appointment' => $appointment->getData(),  // Fetch appointment details
                        'comments' => $doctorCommentCollection->toArray()['items']  // Fetch doctor comments
                    ];
                }
            }
        }

        return $appointmentData;
    }
}
