<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */
class Amasty_Defconf_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Default extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        $this->setTemplate('amdefconf/default.phtml');
        return parent::_prepareLayout();
    }
    
    public function getSelectedId()
    {
        $selected = (string) Mage::getStoreConfig('amdefconf/configurable/preselect');
        if ($selected)
        {
            $selectedIds = unserialize($selected);
            $product     = Mage::registry('current_product');
            if ($product && isset($selectedIds[$product->getId()]))
            {
                return $selectedIds[$product->getId()];
            }
        }
        return '';
    }
}