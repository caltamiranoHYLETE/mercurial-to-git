<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */


class Amasty_Defconf_Model_Source_Preselect_Options extends Varien_Object
{
    const PRESELECT_DISABLE = 0;

    const PRESELECT_FIRST_OPTIONS = 1;

    const PRESELECT_CHEAPEST_PRODUCT = 2;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amdefconf');
        return array(
            array('value' => self::PRESELECT_DISABLE, 'label' => $hlp->__('No')),
            array('value' => self::PRESELECT_FIRST_OPTIONS, 'label' => $hlp->__('First Options')),
            array('value' => self::PRESELECT_CHEAPEST_PRODUCT, 'label' => $hlp->__('Cheapest Product')),
        );
    }

}
