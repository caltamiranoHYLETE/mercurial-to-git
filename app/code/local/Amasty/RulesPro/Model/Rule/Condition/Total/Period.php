<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


class Amasty_RulesPro_Model_Rule_Condition_Total_Period extends Mage_Rule_Model_Condition_Abstract {

    public function loadOperatorOptions() 
    {
        $this->setOperatorOption(array(
            '>=' => Mage::helper('rule')->__('equals or less than'),
            '<=' => Mage::helper('rule')->__('equals or greater than'),
            '>'  => Mage::helper('rule')->__('less than'),
            '<'  => Mage::helper('rule')->__('greater than'),
            '='  => Mage::helper('rule')->__('is'),
        ));
        
        return $this;
    }

    public function asHtml() 
    {
        $html = $this->getTypeElement()->getHtml() .
                Mage::helper('amrules')->__("Period after order was placed %s %s  day(s)", $this->getOperatorElement()->getHtml(), $this->getValueElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function validate(Varien_Object $object) 
    {
        $v = min(16000, $this->getValue()); // on windows can work incorrect for very big values.
        
        $date = date("Y-m-d H:i:s", time() - $v * 24 * 3600);
        $result = array('date' => $this->getOperatorForValidate() . "'" . $date . "'");
        
        return $result;
    }

}

