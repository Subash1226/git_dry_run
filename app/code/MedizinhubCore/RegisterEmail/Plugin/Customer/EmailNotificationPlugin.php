<?php
namespace MedizinhubCore\RegisterEmail\Plugin\Customer;

class EmailNotificationPlugin
{
    /**
     * Prevent sending new account email after registration
     *
     * @param \Magento\Customer\Model\EmailNotification $subject
     * @param callable $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string|null $sendemailStoreId
     * @return void
     */
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotification $subject,
        callable $proceed,
        $customer,
        $type = \Magento\Customer\Model\EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ) {
        return;
    }
}
