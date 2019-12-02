<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */
class Amasty_Defconf_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSelectedProduct($parent)
    {
        $selectedProductId = 0;
        $selected = (string) Mage::getStoreConfig('amdefconf/configurable/preselect');
        if ($selected) {
            $selectedIds = unserialize($selected);
            if (isset($selectedIds[$parent->getId()])) {
                $selectedProductId = $selectedIds[$parent->getId()];
            }
        }

        if (Mage::app()->getRequest()->getParam('sel')) {
            // can load both by ID or SKU
            $possibleSku       = Mage::app()->getRequest()->getParam('sel');
            $selectedProductId = Mage::getModel('catalog/product')->getIdBySku($possibleSku);
            if (!$selectedProductId) {
                $selectedProductId = Mage::app()->getRequest()->getParam('sel');
            }
        }

        return $selectedProductId;
    }

    public function preselectFirstOptions()
    {
        return Mage::getStoreConfig('amdefconf/general/preselect')
            == Amasty_Defconf_Model_Source_Preselect_Options::PRESELECT_FIRST_OPTIONS;
    }

    public function preselectCheapestProduct()
    {
        return Mage::getStoreConfig('amdefconf/general/preselect')
            == Amasty_Defconf_Model_Source_Preselect_Options::PRESELECT_CHEAPEST_PRODUCT;
    }
}
