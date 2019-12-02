<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */


class Amasty_Methods_Helper_Payment_Data extends Mage_Payment_Helper_Data
{
    const PAYMENT = 'payment';

    public function getStoreMethods($store = null, $quote = null)
    {
        $methods = parent::getStoreMethods($store, $quote);
        if (!$quote) {
            return $methods;
        }

        if ( Mage::helper('ammethods')->isNullGroups(self::PAYMENT)) {
            $newMethods = array();
            $customer = $quote->getCustomer();
            foreach ($methods as $k => $method) {
                if (!Mage::helper('ammethods')->canUseMethod($method, self::PAYMENT, $customer)) {
                    continue;
                }
                $newMethods[$k] = $method;
            }
            $methods = $newMethods;
        }

        return $methods;
    }
}