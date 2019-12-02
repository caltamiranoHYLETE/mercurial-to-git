<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Mysql4_Banners extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('amrules/banners', 'entity_id');
    }
}