<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


class  Amasty_RulesPro_Model_Rule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address
{
    public function loadAttributeOptions()
    {
        $helper = Mage::helper('amrules');
        $attributes = $this->getAttributeOption();
//        $attributes['base_subtotal_with_discount'] = $helper->__('Subtotal After Discount');
        $attributes['base_subtotal_without_special_price'] = $helper->__('Subtotal Without Special Price');
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * @see Mage_SalesRule_Model_Rule_Condition_Address::getInputType()
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * Add field "base_subtotal_with_discount" to address.
     * It is need to validate the "base_subtotal_with_discount" attribute
     * @see Mage_SalesRule_Model_Rule_Condition_Address::validate()
     */
    public function validate(Varien_Object $address)
    {
        $this->_collectTotalWithoutSpecialPrice($address);

        $subAfterDiscount = $address->getBaseSubtotal() + $address->getDiscountAmount();
        $address->setData('base_subtotal_with_discount', $subAfterDiscount);

        return parent::validate($address);
    }

    /**
     * @param Varien_Object $address
     *
     * @return $this
     */
    protected function _collectTotalWithoutSpecialPrice(Varien_Object $address)
    {
        if ($this->getAttribute() == 'base_subtotal_without_special_price'
            && !$address->hasData('base_subtotal_without_special_price')
        ) {
            $totals = array();
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($address->getAllItems() as $item) {
                // $item can be child
                if ($this->_isSpecialPrice($item->getProduct())) {
                    if ($item->getParentItemId()) {
                        unset($totals[$item->getParentItemId()]);
                    }
                    continue;
                }
                if (!$item->getParentItemId()) {
                    $totals[$item->getItemId()] = $item->getBaseRowTotal();
                }
            }

            $address->setData('base_subtotal_without_special_price', array_sum($totals));
        }

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    protected function _isSpecialPrice($product)
    {
        // is Special Price active by date interval
        $isSpecialPriceActive = Mage::app()->getLocale()->isStoreDateInInterval(
            Mage::app()->getStore(),
            $product->getSpecialFromDate(),
            $product->getSpecialToDate()
        );

        return ($isSpecialPriceActive && $product->getSpecialPrice());
    }
}
