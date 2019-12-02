<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */




class Amasty_RulesPro_Model_Customersegment_Observer extends Enterprise_CustomerSegment_Model_Observer
{
    /**
     * Add Customer Segment condition to the salesrule management
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSegmentsToSalesRuleCombine(Varien_Event_Observer $observer)
    {

        if (!Mage::helper('enterprise_customersegment')->isEnabled()) {
            return;
        }

        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)){
            $cond = array();
        }

        $cond[] = array(
                'value' => 'enterprise_customersegment/segment_condition_segment',
                'label' => Mage::helper('enterprise_customersegment')->__('Customer Segment'),
        );

        $transport->setConditions($cond);

    }
}
