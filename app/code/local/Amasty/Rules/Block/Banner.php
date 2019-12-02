<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

/**
 * Class Amasty_Promo_Block_Banner
 */
class Amasty_Rules_Block_Banner extends Mage_Catalog_Block_Product_Abstract
{
    /*
     * this class is deprecated
     * we are not supporting this class
     * */
    /**
     * Getting shopping rule modes
     */
    const MODE_PRODUCT = 'product';
    const MODE_CART    = 'cart';

    /** @var Mage_SalesRule_Model_Rule[] */
    protected $_validRules;

    /** @var Mage_SalesRule_Model_Resource_Rule_Collection  */
    protected $_rulesCollection;

    /**
     * @return Mage_SalesRule_Model_Rule[]
     */
    protected function _getValidRules()
    {
        if ($this->_validRules === null) {
            $this->_validRules = array();
            /**
             * product mode based on current product (faster mode), useful for FPC
             * cart mode use quote, can take some time for validation
             */
            if (Mage::getStoreConfig('amrules/banners/mode') === self::MODE_PRODUCT) {
                $this->_validRules = $this->_getProductBasedValidRule();
            } else if (Mage::getStoreConfig('amrules/banners/mode') === self::MODE_CART) {
                $this->_validRules = $this->_getQuoteBasedValidRule();
            } else {
                $this->_validRules = new Varien_Object();
            }
        }

        return $this->_validRules;
    }

    /**
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    protected function _getRulesCollection()
    {
        if(!$this->_rulesCollection){
            $this->_rulesCollection = Mage::getModel('amrules/banners')->getRulesCollection();
        }

        return $this->_rulesCollection;
    }

    /**
     * @return Mage_SalesRule_Model_Rule[]
     */
    protected function _getProductBasedValidRule()
    {
        $currentQuote = Mage::getModel('checkout/cart')->getQuote();

        $quoteItem = new Varien_Object();
        $quoteItem->setProduct($this->getProduct());
        $quoteItem->setStoreId(Mage::getModel('checkout/cart')->getQuote()->getStoreId());
        $quoteItem->setIsVirtual(false);
        $quoteItem->setQuote($currentQuote);
        $quoteItem->setAllItems($currentQuote->getAllItems());

        foreach($this->_getRulesCollection() as $rule){
            if ($rule->validate($quoteItem)
                && $rule->getActions()->validate($quoteItem)
            ) {
                $this->_validRules[] = $rule;
            }
        }

        return $this->_validRules;
    }

    /**
     * @return Mage_SalesRule_Model_Rule[]
     */
    protected function _getQuoteBasedValidRule()
    {
        /*avoid out of stock exception */
        if (Mage::helper('amrules')->checkAvailableQty($this->getProduct(),1)) {
            $product = $this->getProduct();

            if ($product->getTypeId() === 'configurable'){
                $childrenProducts = $product->getChildrenProducts();
                foreach ($childrenProducts as $key => $childProduct){
                    if (!Mage::helper('amrules')->checkAvailableQty($childProduct,1)) {
                        unset($childrenProducts[$key]);
                    }
                }
                if (count($childrenProducts) > 0){
                    $product = end($childrenProducts);
                    $product = Mage::getModel('catalog/product')->load($product->getId());
                }
            }

            $currentQuote = Mage::getModel('checkout/cart')->getQuote();

            $afterQuote = Mage::getModel('sales/quote');
            $afterQuote->addProduct($product);
            $afterQuote->merge($currentQuote);
            $afterQuote->collectTotals();

            $currentRules = array();

            /**
             * validate rules according to current quote
             */
            foreach ($this->_getRulesCollection() as $rule) {
                foreach ($currentQuote->getItemsCollection() as $item) {
                    if ($item->getProduct()->getId() == $this->getProduct()->getId()
                        && $rule->getActions()->validate($item)
                    ) {
                        $currentRules[] = $rule->getId();
                    }
                }
            }

            /**
             * match with quote after add current product
             */
            foreach ($this->_getRulesCollection() as $rule) {
                if (!in_array($rule->getId(), $currentRules)) {
                    foreach ($afterQuote->getItemsCollection() as $item) {
                        if ($item->getProduct()->getId() == $product->getId()
                            && $rule->getActions()->validate($item)
                        ) {
                            $this->_validRules[] = $rule;
                        }
                    }
                }
            }
        }

        return $this->_validRules;
    }

    /**
     * Get list of matched rules according to settings
     * @return Mage_SalesRule_Model_Rule[]
     */
    public function getValidRules()
    {
        $validRules = $this->_getValidRules();
        if (Mage::getStoreConfig('amrules/banners/single') === '1'
            && count($validRules) > 0
        ) {
            return array_slice($validRules, 0, 1);
        }

        return $validRules;
    }

    /**
     * Get top-priority validate rule, compatibility for themes before 2.3.6
     * @return Mage_SalesRule_Model_Rule|Varien_Object
     */
    protected function _getValidRule()
    {
        $validRule = new Varien_Object();
        $validRuels  = $this->_getValidRules();
        if (count($validRuels) > 0
            && array_key_exists(0, $validRuels)
        ) {
            $validRule = $validRuels[0];
        }

        return $validRule;
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed
     */
    function getDescription(Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }
        $position = $this->getPosition() . '_banner_description';
        $result = $validRule->getData($position);

        return $result;
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed
     */
    function getImage(Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }
        $position = $this->getPosition() . '_banner_img';
        $result = Mage::helper("amrules/image")->getLink($validRule->getData($position));

        return $result;
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed
     */
    function getAlt(Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }
        $position = $this->getPosition() . '_banner_alt';
        $result = $validRule->getData($position);

        return $result;
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed
     */
    function getHoverText(Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }
        $position = $this->getPosition() . '_banner_hover_text';
        $result = $validRule->getData($position);

        return $result;
    }

    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed|string
     */
    function getLink(Mage_SalesRule_Model_Rule $validRule = null)
    {
        if ($validRule === null) {
            $validRule = $this->_getValidRule();
        }

        $position = $this->getPosition() . '_banner_link';
        $result = $validRule->getData($position) ? $validRule->getData($position) : "#";

        return $result;
    }
}