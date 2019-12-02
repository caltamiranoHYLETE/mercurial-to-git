<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */


class Amasty_Methods_Model_Rewrite_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        parent::collectRates($request);

        $result = $this->getResult();
        Mage::dispatchEvent('am_methods_rates', array(
            'request' => $request,
            'result' => $result,
        ));

        return $this;
    }
}