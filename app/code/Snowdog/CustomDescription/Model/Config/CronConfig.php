<?php
namespace Snowdog\CustomDescription\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class CronConfig extends Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/update_product_attributes/schedule/cron_expr';

    protected function _afterSave()
    {
        $time = $this->getData('groups/cron/fields/update_product_attributes_time/value');
        $frequency = $this->getData('groups/cron/fields/update_product_attributes_frequency/value');

        $cronExprArray = [
            intval($time[1]), // Minute
            intval($time[0]), // Hour
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*', // Day of the Month
            '*', // Month of the Year
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*', // Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->configWriter->save(self::CRON_STRING_PATH, $cronExprString);
        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t save the cron expression.'));
        }

        return parent::_afterSave();
    }
}