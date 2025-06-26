<?php
namespace Mhb\RestrictCod\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Config path constants
     */
    const XML_PATH_RESTRICTCOD_ACTIVE = 'payment/restrictcod/active';
    const XML_PATH_RESTRICTCOD_STATES = 'payment/restrictcod/states';

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RESTRICTCOD_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get allowed states for COD
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAllowedStates($storeId = null)
    {
        $states = $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICTCOD_STATES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $states ? explode(',', $states) : [];
    }

    /**
     * Check if COD is allowed for given state
     *
     * @param string $stateCode
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowedForState($stateCode, $storeId = null)
    {
        if (!$this->isEnabled($storeId)) {
            return true; // If module is disabled, COD is allowed for all states
        }

        $allowedStates = $this->getAllowedStates($storeId);
        
        // If no states specified, COD is allowed for all states
        if (empty($allowedStates)) {
            return true;
        }

        return in_array($stateCode, $allowedStates);
    }
}