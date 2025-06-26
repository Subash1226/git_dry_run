<?php
namespace MedizinhubCore\Offers\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use MedizinhubCore\Offers\Block\Offers as OffersBlock;

class CouponDetails extends Action
{
    protected $offersBlock;

    public function __construct(
        Context $context,
        OffersBlock $offersBlock
    ) {
        parent::__construct($context);
        $this->offersBlock = $offersBlock;
    }

    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $couponDetails = $this->offersBlock->getCouponDetails($ruleId);

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($couponDetails);
        return $result;
    }
}