<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */
class Amasty_Defconf_Block_Js extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        $this->setTemplate('amdefconf/js.phtml');
        return parent::_prepareLayout();
    }
    
    protected function _toHtml()
    {
        $product = Mage::registry('current_product');
        if ($product && $product->isConfigurable())
        {
            return parent::_toHtml();
        }
        return '';
    }
    
    /**
    * will return a string like "3, 7, 1"
    * to be used to call JS function
    */
    public function getAttributeIdsString()
    {
        $product = Mage::registry('current_product');
        $selectedProductId = $this->getAmastyHelper()->getSelectedProduct($product);
        $selectedString    = '';

        if ($selectedProductId || $this->getAmastyHelper()->preselectCheapestProduct()) {
            $configurableBlock = Mage::app()->getLayout()->createBlock(
                'catalog/product_view_type_configurable',
                'configurable.block'
            );

            if ($product = $this->getMatchedProduct($configurableBlock, $selectedProductId)) {
                foreach ($configurableBlock->getAllowAttributes() as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue = $product->getData($productAttribute->getAttributeCode());
                    $selectedString .= $attributeValue . ', ';
                }
                $selectedString = rtrim($selectedString, ", ");
            }
        }
        
        return $selectedString;
    }

    public function preselectFirstOptions()
    {
        return $this->getAmastyHelper()->preselectFirstOptions();
    }

    /**
     * @return Amasty_Defconf_Helper_Data
     */
    public function getAmastyHelper()
    {
        return Mage::helper('amdefconf');
    }

    public function getAmconfImageUlr()
    {
        $url = '';
        if (Mage::getStoreConfig('amdefconf/general/preload_image')) {
            $product = Mage::registry('current_product');
            $selectedProductId = $this->getAmastyHelper()->getSelectedProduct($product);
            $url = $this->getUrl('amconf/ajax', array('id' => $selectedProductId));;
        }

        return $url;
    }

    private function getMatchedProduct($configurableBlock, $selectedProductId)
    {
        $selectedProduct = null;

        if ($selectedProductId) {
            foreach ($configurableBlock->getAllowProducts() as $product) {
                $productId = $product->getId();
                if ($productId == $selectedProductId) {
                    $selectedProduct = $product;
                    break;
                }
            }
        } else {
            foreach ($configurableBlock->getAllowProducts() as $product) {
                if (!$selectedProduct || $product->getFinalPrice() < $selectedProduct->getFinalPrice()) {
                    $selectedProduct = $product;
                }
            }
        }

        return $selectedProduct;
    }
}
