<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Model_Observer
{

    const SHIPPING = 'shipping';

    public function changeRates($observer)
    {
        $request = $observer->getRequest();

        $result = $observer->getResult();
        $rates = $result->getAllRates();

        if (!count($rates)) {
            return $this;
        }
        if ( Mage::helper('ammethods')->isNullGroups(self::SHIPPING)) {
            $result->reset();

            $count = 0;
            foreach ($rates as $methodCode => $rate) {
                $method = $rate->getData();
                if (Mage::helper('ammethods')->canUseMethod($method['carrier'], self::SHIPPING)) {
                    $result->append($rate);
                    $count++;
                }
            }
        }

        return $this;
    }
}