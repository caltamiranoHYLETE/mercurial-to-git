<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

class Amasty_Rules_Model_Source_Banner_Mode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Amasty_Rules_Block_Banner::MODE_PRODUCT,
                'label' => Mage::helper('amrules')->__('Product')
            ),
            array(
                'value' => Amasty_Rules_Block_Banner::MODE_CART,
                'label' => Mage::helper('amrules')->__('Cart')
            )
        );
    }
}