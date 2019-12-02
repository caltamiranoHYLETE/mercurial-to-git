<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Defconf
 */


class Amasty_Defconf_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    protected $_currentProduct;

    public function getProduct()
    {
        if (!$this->_currentProduct) {
            $product = parent::getProduct();
            if ($product->isConfigurable() && Mage::getStoreConfig('amdefconf/general/preload_image')) {
                $selectedProductId = Mage::helper('amdefconf')->getSelectedProduct($product);
                if (empty($selectedProductId) && Mage::getStoreConfig('amdefconf/general/enable')) {
                    $configurableBlock = $this->getLayout()->getBlockSingleton('catalog/product_view_type_configurable');
                    $jsonConfig = $configurableBlock->getJsonConfig();
                    $config = Mage::helper('core')->jsonDecode($jsonConfig);
                    if (isset($config['attributes'])) {
                        $attributes = $config['attributes'];
                        $firstAttribute = array_shift($attributes);
                        if (isset($firstAttribute['options']) && isset($firstAttribute['options'][0]['products'])) {
                            $selectedProductId = (int) $firstAttribute['options'][0]['products'][0];
                        }
                    }
                }
                if ($selectedProductId) {
                    $product = Mage::getModel('catalog/product')
                        ->setStoreId($product->getStoreId())->load($selectedProductId);
                }
            }

            $this->_currentProduct = $product;
        }

        return $this->_currentProduct;
    }
}