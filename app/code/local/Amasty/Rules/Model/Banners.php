<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Banners extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('amrules/banners');
    }

    /**
     * @param $id
     * @return Mage_Core_Model_Abstract
     */
    public function loadByRuleId($id)
    {
        return $this->load($id, 'rule_id');
    }

    /**
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function getRulesCollection()
    {

        $quote = Mage::getModel('checkout/cart')->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());

        $rulesCollection = Mage::getModel('salesrule/rule')
            ->getCollection()
            ->setValidationFilter($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $rulesCollection->getSelect()
            ->joinLeft(
                array('banners' => $rulesCollection->getTable('amrules/banners')),
                'main_table.rule_id = banners.rule_id',
                '*'
            );

        return $rulesCollection;
    }
}