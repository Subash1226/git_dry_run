<?php
namespace Dynamic\ConsultationFee\Api;

interface UpdatePrescriptionStatusInterface
{
    /**
     * @param int $cartId
     * @param bool $isPrescriptionRequired
     * @return array
     */
    public function execute($cartId, $isPrescriptionRequired);
}
