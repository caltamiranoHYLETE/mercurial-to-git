<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Model_Resource_Banners extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('ambannerslite/banners', 'ambanner_id');
    }

    public function getProducts($ruleId)
    {
        $read   = $this->_getReadAdapter();
        $tbl    = $this->getTable('ambannerslite/banners');
        $select = $read->select()->from($tbl, 'ambannerslite_banner_products')->where('rule_id = ?', $ruleId);

        $col = $read->fetchCol($select);

        return $col;
    }
}