<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Block_Checkout_Discount extends Mage_Checkout_Block_Total_Default
{
    protected function _construct()
    {
        if (Mage::getStoreConfig('amrules/breakdown_settings/breakdown'))
            $this->_template = 'amrules/checkout/discount.phtml';
            
        parent::_construct();
    }    
}