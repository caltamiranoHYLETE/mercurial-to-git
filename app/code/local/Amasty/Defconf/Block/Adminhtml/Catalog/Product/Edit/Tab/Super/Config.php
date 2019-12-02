<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */
class Amasty_Defconf_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html = str_replace('<div id="super_product_links">', $this->getLayout()->createBlock('amdefconf/adminhtml_catalog_product_edit_tab_super_default', 'amdefconf.default')->toHtml() . '<div id="super_product_links">', $html);
        return $html;
    }
}