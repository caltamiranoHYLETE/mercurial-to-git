<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Model_Banners extends Mage_Core_Model_Abstract
{
    const DISABLE = 0;
    const ENABLE = 1;

    const PRODUCT = 0;
    const SKU = 1;
    const CATEGORY = 2;

    protected $_notNullCondition = array('notnull' => true);

    public function _construct()
    {
        $this->_init('ambannerslite/banners');
    }

    /**
     * @param $id
     *
     * @return Mage_Core_Model_Abstract
     */
    public function loadByRuleId($id)
    {
        return $this->load($id, 'rule_id');
    }

    public function getProducts($ruleId)
    {
        $ids = $this->getResource()->getProducts($ruleId);

        return $ids;
    }

    public function getRulesCollection()
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $storeId = Mage::app()->getStore()->getId();
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        $rulesCollection = Mage::getModel('salesrule/rule')->getCollection();
        /**
         *validate by store id and group id
         */
        $rulesCollection = $rulesCollection->addWebsiteGroupDateFilter($websiteId, $groupId);

        $rulesCollection->getSelect()->joinInner(
            array(
            'banners' => $rulesCollection->getTable('ambannerslite/banners')),
            'main_table.rule_id = banners.rule_id',
            '*'
        );

        if (!Mage::app()->isSingleStoreMode() && Mage::helper('ambase')->isModuleActive('Amasty_Promo')) {
            /**
             * check stores filter
             * if rule don't have selected stores, they should be available too
             * current store matched, stores filter not initialized or all stores options selected (0 value)
             */
            $rulesCollection->getSelect()->where(
                "FIND_IN_SET ('{$storeId}', amstore_ids) or amstore_ids = '' or FIND_IN_SET (0, amstore_ids)"
            );
        }

        return $rulesCollection;
    }

    public function getCollectionWithBannersFromPromo()
    {
        $collectionWithBanners = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter(
                array(
                    'ampromo_top_banner_enable',
                    'ampromo_after_name_banner_enable',
                    'ampromo_top_banner_img',
                    'ampromo_top_banner_description',
                    'ampromo_top_banner_alt',
                    'ampromo_top_banner_hover_text',
                    'ampromo_top_banner_link',
                    'ampromo_top_banner_gift_images',
                    'ampromo_after_name_banner_img',
                    'ampromo_after_name_banner_description',
                    'ampromo_after_name_banner_alt',
                    'ampromo_after_name_banner_hover_text',
                    'ampromo_after_name_banner_link',
                    'ampromo_after_name_banner_gift_images',
                    'ampromo_label_img',
                    'ampromo_label_alt',
                ),
                array(
                    array('neq' => 0),
                    array('neq' => 0),
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    array('neq' => 0),
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                    array('neq' => 0),
                    $this->_notNullCondition,
                    $this->_notNullCondition,
                )
            );

        return $collectionWithBanners;
    }

    public function getCollectionWithBannersFromRules()
    {
        $ruleCollection = Mage::getModel('salesrule/rule')->getCollection();
        $ruleCollection->getSelect()
            ->join(
                array('banners' => $ruleCollection->getTable('amrules/banners')),
                'main_table.rule_id = banners.rule_id',
                '*'
            );
        $ruleCollection->addFieldToFilter(
            array(
                'top_banner_description',
                'top_banner_img',
                'top_banner_alt',
                'top_banner_hover_text',
                'top_banner_link',
                'after_name_banner_description',
                'after_name_banner_img',
                'after_name_banner_alt',
                'after_name_banner_hover_text',
                'after_name_banner_link',
                'label_img',
                'label_alt',
            ),
            array(
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
                $this->_notNullCondition,
            )
        );

        return $ruleCollection;
    }
}
