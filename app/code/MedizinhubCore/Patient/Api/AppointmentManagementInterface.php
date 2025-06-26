<?php
namespace MedizinhubCore\Patient\Api;

interface AppointmentManagementInterface
{
    /**
     * Save appointment with file upload
     *
     * @param int $patientId
     * @param int $practitionerId
     * @param int $hospitalId
     * @param string $appointmentDate
     * @param string $timeSlot
     * @param string $patientIssue
     * @param array $files Base64 encoded files array
     * @return mixed
     */
    public function saveAppointment(
        $patientId,
        $practitionerId,
        $hospitalId,
        $appointmentDate,
        $timeSlot,
        $patientIssue,
        $files = []
    );
}
