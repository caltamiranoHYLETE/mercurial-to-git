<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */
class Amasty_Defconf_Model_Observer
{
    public function onCatalogProductSaveAfter($obs)
    {
        $product = $obs->getProduct();
        if ($product->getId() && $product->isConfigurable())
        {
            /*compatibility with Amasty Grid*/
            $params = Mage::app()->getRequest()->getParams();
            if(!array_key_exists('default_configurable_preselect_id', $params)){
                return false;
            }
            $selectedId = Mage::app()->getRequest()->getParam('default_configurable_preselect_id');
            $config     = Mage::getModel('core/config');
            $selected   = (string) Mage::getStoreConfig('amdefconf/configurable/preselect');
            if ($selected)
            {
                $selectedIds = unserialize($selected);
            } else 
            {
                $selectedIds = array();
            }
            // will re-save only if it's not set yes, or is different from the new value
            if ($selectedId && ( !isset($selectedIds[$product->getId()]) || $selectedIds[$product->getId()] != $selectedId ) )
            {
                $selectedIds[$product->getId()] = $selectedId;
                $config->saveConfig('amdefconf/configurable/preselect', serialize($selectedIds));
                $config->cleanCache();
            }
            if (!$selectedId)
            {
                if (isset($selectedIds[$product->getId()]))
                {
                    unset($selectedIds[$product->getId()]);
                }
                $config->saveConfig('amdefconf/configurable/preselect', serialize($selectedIds));
                $config->cleanCache();
            }
        }
    }
}