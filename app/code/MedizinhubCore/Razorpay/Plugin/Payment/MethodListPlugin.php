<?php
namespace MedizinhubCore\Razorpay\Plugin\Payment;

use Magento\Payment\Model\MethodList;
use Magento\Payment\Model\Method\AbstractMethod;
use Psr\Log\LoggerInterface;

class MethodListPlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Custom payment method sorting with logging
     *
     * @param MethodList $subject
     * @param array $result
     * @return array
     */
    public function afterGetAvailableMethods(
        MethodList $subject,
        $result
    ) {
        $initialMethodCodes = array_map(function($method) {
            return $method->getCode();
        }, $result);
        
        usort($result, function(AbstractMethod $a, AbstractMethod $b) {
            $priorityA = $this->getMethodPriority($a->getCode());
            $priorityB = $this->getMethodPriority($b->getCode());

            return $priorityA - $priorityB;
        });

        $sortedMethodCodes = array_map(function($method) {
            return $method->getCode();
        }, $result);
        return $result;
    }

    /**
     * Get method priority for sorting
     *
     * @param string $methodCode
     * @return int
     */
    private function getMethodPriority(string $methodCode): int
    {
        $priorities = [
            'razorpay' => 1,
            'cashondelivery' => 2,
            'checkmo' => 3,
        ];

        $priority = $priorities[$methodCode] ?? 999;
        return $priority;
    }
}