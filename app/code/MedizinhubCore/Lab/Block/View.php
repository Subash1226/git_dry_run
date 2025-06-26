<?php
namespace MedizinhubCore\Lab\Block\Test;

use Magento\Framework\View\Element\Template;

class View extends Template
{
    /**
     * @var \MedizinhubCore\Lab\Model\Test
     */
    protected $testData;

    /**
     * Set test data
     *
     * @param \MedizinhubCore\Lab\Model\Test $test
     * @return $this
     */
    public function setTestData($test)
    {
        $this->testData = $test;
        return $this;
    }

    /**
     * Get test data
     *
     * @return \MedizinhubCore\Lab\Model\Test
     */
    public function getTestData()
    {
        return $this->testData;
    }
}
