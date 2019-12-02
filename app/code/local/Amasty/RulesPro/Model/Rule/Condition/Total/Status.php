<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


class Amasty_RulesPro_Model_Rule_Condition_Total_Status extends Mage_Rule_Model_Condition_Abstract {

    public function loadAttributeOptions() 
    {
        $statuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $options = $this->getAttributeOptions();
        foreach ($statuses as $status) {
            $options[$status['status']] = $status['label'];
        }

        $this->setAttributeOption($options);
        return $this;
    }
    
    public function loadOperatorOptions() 
    {
        $this->setOperatorOption(array(
                '='  => Mage::helper('rule')->__('is'),
                '<>' => Mage::helper('rule')->__('is not'),
        ));
        
        return $this;
    }

    public function asHtml() 
    {
        $html = $this->getTypeElement()->getHtml() .
                Mage::helper('amrules')->__("Order Status %s %s", $this->getOperatorElement()->getHtml(), $this->getAttributeElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function validate(Varien_Object $object) 
    {
        $result = array('status' => $this->getOperatorForValidate() . "'" . $this->getAttributeElement()->getValue() . "'");
        return $result;
    }

}

