<?php

namespace Dynamic\ConsultationFee\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\Totals as MagentoTotals;

class Totals extends MagentoTotals
{
    /**
     * Initialize order totals
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $order = $this->getOrder();
        $consultationFee = $order->getData('doctor_consultation_fee');

        if ($consultationFee) {
            $this->_totals['doctor_consultation_fee'] = new \Magento\Framework\DataObject([
                'code'  => 'doctor_consultation_fee',
                'strong' => true,
                'value' => $consultationFee,
                'label' => __('Doctor Consultation Fee'),
            ]);
        }
        return $this;
    }
}
