<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RuleTimeConditions
 */

if (Mage::helper('core')->isModuleEnabled('Amasty_Xcoupon')) {
    $autoloader = new \Varien_Autoload();
    $autoloader->autoload('Amasty_RuleTimeConditions_Model_Salesrule_Mysql4_Rule_Collection_Xcoupon');
} elseif (Mage::helper('core')->isModuleEnabled('Amasty_Coupons')) {
    $autoloader = new \Varien_Autoload();
    $autoloader->autoload('Amasty_RuleTimeConditions_Model_Salesrule_Mysql4_Rule_Collection_Mcoupon');
} else {
    class Amasty_RuleTimeConditions_Model_Resource_Rule_Pure extends Mage_SalesRule_Model_Resource_Rule_Collection {}
}
