<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Block_Banner extends Mage_Catalog_Block_Product_Abstract
{
    /** @var Mage_SalesRule_Model_Rule[] */
    protected $_validRules;

    protected $_validRule;

    /** @var Mage_SalesRule_Model_Resource_Rule_Collection */
    protected $_rulesCollection;

    /**
     * Get Valid Rules List
     *
     * @return Mage_SalesRule_Model_Rule[]
     */
    protected function _getValidRules()
    {
        if ($this->_validRules === null) {
            $helper = $this->helper('ambannerslite');
            if (Mage::helper('ambase')->isModuleActive('Amasty_Promo') && !$helper->checkRulesForBanners()) {
                $this->_validRules = $this->_getRules();
            } else if (Mage::helper('ambase')->isModuleActive('Amasty_Rules')
                       && !$helper->checkRulesForBannersRules()
            ) {
                $this->_validRules = $this->_getRules();
            } else {
                $this->_validRules = new Varien_Object();
            }
        }

        return $this->_validRules;
    }

    /**
     * @return array|Mage_SalesRule_Model_Rule[]
     */
    public function getValidRules()
    {
        $validRules = $this->_getValidRules();
        $storeConfig = Mage::getStoreConfig('ambannerslite/banners/single');
        if ($storeConfig === '1' && count($validRules) > 0) {
            return array_slice($validRules, 0, 1);
        }

        return $validRules;
    }

    /**
     * @return Mage_SalesRule_Model_Rule[]
     */
    protected function _getRules()
    {
        if (!$this->_rulesCollection) {
            $this->_rulesCollection = $this->_getRulesCollection();
            foreach ($this->_rulesCollection as $rule) {
                $product = $this->getProduct();
                $validateProduct = $this->_validateProduct($product, $rule);
                if ($validateProduct) {
                    $this->_validRules[] = $rule;
                }
            }
        }

        return $this->_validRules;
    }

    /**
     * Get Amasty Promo Rules Collection Filtered by website_id customer_group_id coupon_code
     *
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    protected function _getRulesCollection()
    {
        if (!$this->_rulesCollection) {
            $this->_rulesCollection = Mage::getModel('ambannerslite/banners')->getRulesCollection();
        }

        return $this->_rulesCollection;
    }

    /**
     * validate product by rules
     * check banners type
     * @param $product
     * @param $rule
     *
     * @return bool
     */
    protected function _validateProduct($product, $rule)
    {
        $result = false;
        $bannerType = $rule->getAmbannersliteBannerType();
        switch ($bannerType) {
            case Amasty_PromoBannersLite_Model_Banners::PRODUCT :
                $result = true;
                break;
            case Amasty_PromoBannersLite_Model_Banners::SKU :
                if ($this->_checkProductsById($rule, $product)) {
                    $result = true;
                }
                break;
            case Amasty_PromoBannersLite_Model_Banners::CATEGORY :
                if ($this->_checkProductsByCategoryId($rule, $product)) {
                    $result = true;
                }
                break;
        }
        return $result;
    }

    protected function _checkProductsByCategoryId($rule, $product)
    {
        $categoryIds = explode(',', $rule->getAmbannersliteBannerCategories());
        $categoryIds  = array_unique($categoryIds);
        array_walk($categoryIds, array($this, 'trimValue'));
        $productCategoryIds = $product->getCategoryIds();

        foreach ($productCategoryIds as $productCategoryId) {
            if (in_array($productCategoryId, $categoryIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * check banners for products ids
     * @param $rule
     * @param $product
     *
     * @return bool
     */
    protected function _checkProductsById($rule, $product)
    {
        $ruleProductsIds = explode(',', $rule->getAmbannersliteBannerProducts());
        array_walk($ruleProductsIds, array($this, 'trimValue'));
        if (in_array($product->getEntityId(), $ruleProductsIds)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * check rule for promo sku
     * if not contains promo sku
     * return false
     * @param $rule
     *
     * @return bool
     */
    public function checkForPromoSku($rule)
    {
        $actionWithPromoSku = array('ampromo_items', 'ampromo_cart', 'ampromo_product', 'ampromo_spent');
        $ruleAction = $rule->getSimpleAction();
        if (in_array($ruleAction, $actionWithPromoSku)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $field
     * @param Mage_SalesRule_Model_Rule|null $validRule
     *
     * @return mixed
     */
    public function getBannerData($field, Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }

        return $validRule->getData('ambannerslite_' . $this->getPosition() . $field);
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed
     */
    public function getImage(Mage_SalesRule_Model_Rule $validRule = null)
    {
        $bannerImg = $this->getBannerData('_banner_img', $validRule);

        return Mage::helper("ambannerslite/image")->getLink($bannerImg);
    }


    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed|string
     */
    public function getLink(Mage_SalesRule_Model_Rule $validRule = null)
    {
        $link = $this->getBannerData('_banner_link', $validRule);

        if ($link) {
            return $link;
        } else {
            return '#';
        }
    }

    /**
     * Get top-priority validate rule, compatibility for themes before 2.3.6
     * @return Mage_SalesRule_Model_Rule|Varien_Object
     */
    protected function _getValidRule()
    {
        if (is_null($this->_validRule)) {
            $validRule = new Varien_Object();
            $validRules = $this->_getValidRules();
            if (count($validRules) > 0 && array_key_exists(0, $validRules)) {
                $validRule = $validRules[0];
            }
            $this->_validRule = $validRule;
        }
        return $this->_validRule;
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return array
     */
    public function getProducts(Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }

        $products = array();
        if ('ampromo_product' == $validRule->getSimpleAction()) {
            return $products;
        }

        $promoSku = $validRule->getPromoSku();
        $skuArray = explode(",", $promoSku);
        array_walk($skuArray, array($this, 'trimValue'));

        if (!empty($promoSku)) {
            $products = Mage::getResourceModel('catalog/product_collection')
                ->addFieldToFilter('sku', array('in' => $skuArray))
                ->addUrlRewrite()
                ->addFinalPrice()
                ->addAttributeToSelect(
                    array(
                        'name',
                        'thumbnail',
                        $this->getStoreConfigData('attribute_header'),
                        $this->getStoreConfigData('attribute_description'))
                )
                ->addAttributeToFilter(
                    'status',
                    array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                );
            $products = $this->applyPromoQty($products, $skuArray);
        }

        return $products;
    }

    /**
     * Apply Promo Qty to Products Collection
     *
     * @param $products
     * @param $skuArray
     *
     * @return
     *
     */
    public function applyPromoQty($products, $skuArray)
    {
        $promoSkuCounts = array_count_values($skuArray);

        foreach ($products as $product) {
            $product->setPromoQty($promoSkuCounts[$product->getSku()]);
        }

        return $products;
    }

    /**
     * @param $value
     */
    public function trimValue(&$value)
    {
        $value = trim($value);
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getStoreConfigData($field)
    {
        return Mage::getStoreConfig('ambannerslite/gift_images/' . $field);
    }

    public function existBanner($rule)
    {
        if ($this->getBannerData('_banner_description', $rule)
            || $this->getImage($rule)
            || $this->getBannerData('_banner_gift_images_enable', $rule)) {
            return true;
        }
        return false;
    }

    public function enablePosition($rule)
    {
        if (($this->getPosition() == 'top'
                && $rule->getAmbannersliteTopBannerEnable())
            || ($this->getPosition() == 'after_name'
                && $rule->getAmbannersliteAfterNameBannerEnable())) {
            return true;
        }
        return false;
    }
}
