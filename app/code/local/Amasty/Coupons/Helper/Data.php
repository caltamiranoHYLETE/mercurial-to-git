<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_Helper_Data extends Mage_Core_Helper_Abstract
{
    function isAmastyOnestepcheckoutInstalled(){
        return (string)Mage::getConfig()->getNode('modules/Amasty_Scheckout/active') == 'true';
    }
}