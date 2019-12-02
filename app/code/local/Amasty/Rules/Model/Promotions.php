<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_Promotions
{
    protected $_discount = array();
    public $itemsWithDiscount = null;
    protected $_ruleDiscount = array();


    /**
     * @param $observer
     *
     * @return bool
     */
    public function process($observer)
    {
        $rule = $observer->getEvent()->getRule();
        $item = $observer->getEvent()->getItem();

        if (!$item->getId()) {
            return false;
        }

        Mage::helper('amrules')->addPassedItem($item->getId());

        $address = $observer->getEvent()->getAddress();
        $quote = $observer->getEvent()->getQuote();
        $itemId = $item->getId();
        $result = $observer->getEvent()->getResult();

        $amountToDisplay = 0;
        $isSpecialPromotions = false;
        $types = Mage::helper('amrules')->getDiscountTypes(true);
        $isset = isset($types[$rule->getSimpleAction()]);
        if ($isset) {
            if (!isset($this->_discount[$rule->getId()])) {
                $action = $rule->getSimpleAction();
                if (in_array($action, Mage::helper('amrules')->getNoneMaxDiscountRules())) {
                    $rule->setMaxDiscount(0);
                }
                $className = str_replace('_', '', $rule->getSimpleAction());
                $ruleProcessor = Mage::getSingleton(
                    'amrules_discount/' . $className
                );

                $ruleProcessor->setPriceSelector($rule->getPriceSelector());

                $discount = $ruleProcessor->calculateDiscount(
                    $rule, $address, $quote
                );

                if (in_array($action, Mage::helper('amrules')->getFixedRules())) {
                    foreach ($discount as &$itemDiscount) {
                        if ($itemDiscount['discount'] < 0) {
                            $itemDiscount['discount'] = 0;
                        }
                        if ($itemDiscount['base_discount'] < 0) {
                            $itemDiscount['base_discount'] = 0;
                        }
                    }
                }

                $discount = $ruleProcessor->prepareDiscount($discount, $address, $rule);
                $this->_discount[$rule->getId()] = $discount;

            }

            $r = $this->_discount[$rule->getId()];

            if (!empty($r[$itemId])) {

                $isSpecialPromotions = true;
                isset($r[$item->getId()]['percent'])
                    ? $r[$item->getId()]['percent']
                    : $r[$item->getId()]['percent'] = 0;
                $r[$itemId] = $this->_limitMaxDiscount($r, $rule, $itemId, $quote);
                $amountToDisplay = $r[$itemId]['discount'];

            }
        } else { //it's default rule
            $amountToDisplay = $observer->getEvent()->getResult()->getDiscountAmount();
        }

        if ($this->skip($rule, $item, $address) && $amountToDisplay > 0.0001) {
            $this->unsetDiscount($result, $item);
            return false;
        }

        if ($isSpecialPromotions) {
            $this->setDiscount(
                $result, $item, $r[$itemId]['discount'],
                $r[$itemId]['base_discount'], $r[$itemId]['percent']
            );
        }

        if ($amountToDisplay > 0.0001) {
            $this->_addFullDescription($address, $rule, $item, $amountToDisplay);
        }

        return true;
    }

    /**
     * check all items in quote for promo items
     * @param $rule
     * @param $observer
     *
     * @return bool
     *
     */
    protected function _checkForPack($rule, $observer)
    {
        if ($rule->getPromoSku()) {
            $items = $observer->getEvent()->getQuote()->getAllItems();
            $arrayWithPromoSkus = explode(',', $rule->getPromoSku());
            $arrayWithPromoSkus = $this->_sortAndUniqueArray($arrayWithPromoSkus);
            $arrayWithQuoteSkus = array();
            foreach ($items as $item) {
                $arrayWithQuoteSkus[] = $item->getSku();
            }
            $arrayWithQuoteSkus = $this->_sortAndUniqueArray($arrayWithQuoteSkus);
            $intersection = array_intersect($arrayWithPromoSkus, $arrayWithQuoteSkus);
            if ($arrayWithPromoSkus &&$intersection !== $arrayWithPromoSkus) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $array
     *
     * @return mixed
     */
    protected function _sortAndUniqueArray($array)
    {
        sort($array);
        array_unique($array);

        return $array;
    }

    protected function setDiscount($result, $item, $discount, $baseDiscount, $percent)
    {
        $result->setDiscountAmount($discount);
        $result->setBaseDiscountAmount($baseDiscount);
        if ($percent > 0) {
            $item->setDiscountPercent($percent);
        }
        $item->setIsSpecialPromotion(true);
    }

    protected function unsetDiscount($result, $item)
    {
        $result->setDiscountAmount(0);
        $result->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        $item->setIsSpecialPromotion(false);
    }

    /**
     * determines if we should skip the items with special price or other (in futeure) conditions
     *
     * @param $rule
     * @param $item
     * @param $address
     *
     * @return bool
     */
    public function skip($rule, $item, $address)
    {
        if ($rule->getSimpleAction() == 'cart_fixed') {
            return false;
        }

        $websiteId = Mage::app()->getWebsite()->getId();
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        $origProduct = $item->getProduct();
        $tierPrices = $origProduct->getTierPrice();
        if (Mage::getStoreConfig('amrules/skip/skip_tier_price')) {
            foreach ($tierPrices as $tierPrice) {
                if (($tierPrice['cust_group'] == $groupId
                        || Mage_Customer_Model_Group::CUST_GROUP_ALL == $tierPrice['cust_group'])
                    && $item->getQty() >= $tierPrice['price_qty'] && $websiteId == $tierPrice['website_id']
                ) {
                    return true;
                }
            }
        }

        if ($item->getProductType() == 'bundle') {
            return false;
        }

        if (is_null($this->itemsWithDiscount)) {
            $productIds = array();
            $this->itemsWithDiscount = array();

            foreach (Mage::getSingleton('amrules_discount/abstract')->getAllItems($address) as $addressItem) {
                $productIds[] = $addressItem->getProductId();
            }

            if (!$productIds) {
                return false;
            }

            $productsCollection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addPriceData()
                ->addAttributeToFilter('entity_id', array('in' => $productIds))
                ->addAttributeToFilter(
                    'price', array('gt' => new Zend_Db_Expr('final_price'))
                )
                ->addAttributeToFilter(
                    'special_from_date',
                    array(
                        'or' => array(
                            0 => array('date' => true, 'to' => now()),
                            1 => array('is' => new Zend_Db_Expr('null'))
                        )
                    )
                )
                ->addAttributeToFilter(
                    'special_to_date',
                    array(
                        'or' => array(
                            0 => array('date' => true, 'from' => now()),
                            1 => array('is' => new Zend_Db_Expr('null'))
                        )
                    ),
                    'left'
                );

            foreach ($productsCollection as $product) {
                $this->itemsWithDiscount[] = $product->getId();
            }
        }

        if (Mage::getStoreConfig('amrules/skip/skip_special_price_configurable')) {
            if ($item->getProductType() == "configurable") {
                foreach ($item->getChildren() as $child) {
                    if (in_array($child->getProductId(), $this->itemsWithDiscount)) {
                        return true;
                    }
                }
            }
        }
        switch ($rule->getData('amskip_rule')) {
            case 0:
                if (Mage::getStoreConfig('amrules/skip/skip_special_price')) {
                    if (in_array($item->getProductId(), $this->itemsWithDiscount)) {
                        return true;
                    }
                }
                break;
            case 1:
                if (in_array($item->getProductId(), $this->itemsWithDiscount)) {
                    return true;
                }
                break;
            case 3:
                $price = $item->getDiscountCalculationPrice();
                ($price !== null)
                    ? $price = $item->getBaseDiscountCalculationPrice()
                    : $price = $item->getBaseCalculationPrice();
                $price -= $item->getBaseDiscountAmount();
                if ($item->getProduct()->getPrice() > $price) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Adds a detailed description of the discount
     *
     * @param $address
     * @param $rule
     * @param $item
     * @param $discount
     *
     * @return $this
     */
    protected function _addFullDescription($address, $rule, $item, $discount)
    {
        // we need this to fix double prices with one step checkouts
        $ind = $rule->getId() . '-' . $item->getId();
        if (isset($this->descrPerItem[$ind])) {
            return $this;
        }
        $this->descrPerItem[$ind] = true;

        $descr = $address->getFullDescr();
        if (!is_array($descr)) {
            $descr = array();
        }

        if (empty($descr[$rule->getId()])) {

            $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
            if (!$ruleLabel) {
                if (Mage::helper('ambase')->isModuleActive('Amasty_Coupon')) {
                    if (!$ruleLabel) {
                        $ruleLabel = $rule->getCouponCode(); // possible wrong code, known issue
                    }
                } else { // most frequent case
                    // take into account "generate and import amasty extension"
                    //	UseAutoGeneration
                    if ($rule->getUseAutoGeneration() || $rule->getCouponCode()) {
                        $ruleLabel = $rule->getCouponCode();
                    }
                }
            }

            if (!$ruleLabel) {
                $ruleLabel = $rule->getName();
            }

            $descr[$rule->getId()] =
                array('label' => '<strong>' . htmlspecialchars($ruleLabel) . '</strong>', 'amount' => 0);
        }
        // skip the rule as it adds discount to each item
        // version before 1.4.1 has no class constants for actions
        $skipTypes = array('cart_fixed', Amasty_Rules_Helper_Data::TYPE_AMOUNT);

        if (!in_array($rule->getSimpleAction(), $skipTypes)
            && Mage::getStoreConfig('amrules/breakdown_settings/breakdown_products')
        ) {
            $sep = ($descr[$rule->getId()]['amount'] > 0) ? ', <br/> ' : ': ';
            $descr[$rule->getId()]['label'] = $descr[$rule->getId()]['label']
                . $sep . htmlspecialchars($item->getName());
        }

        $descr[$rule->getId()]['amount'] += $discount;
        $address->setFullDescr($descr);

    }

    protected function _limitMaxDiscount($r, $rule, $itemId, $quote)
    {
        $maxDiscount = $rule->getMaxDiscount();
        if ($maxDiscount == 0) {
            return $r[$itemId];
        }
        if (isset($this->_ruleDiscount[$rule->getId()])) {
            $baseDiscount = $this->_ruleDiscount[$rule->getId()]['base_discount'];
            $baseDiscount += $r[$itemId]['base_discount'];
        } else {
            $baseDiscount = $r[$itemId]['base_discount'];
        }
        if ($baseDiscount > $maxDiscount) {
            $r[$itemId]['base_discount'] = max(0, $r[$itemId]['base_discount'] - ($baseDiscount - $maxDiscount));
            $r[$itemId]['discount'] = max(0, $quote->getStore()->convertPrice($r[$itemId]['base_discount']));
        }
        return $r[$itemId];
    }
}
