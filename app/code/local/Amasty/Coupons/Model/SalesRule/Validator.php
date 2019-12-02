<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_Model_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{
    protected function _maintainAddressCouponCode($address, $rule)
    {
        // Rule is a part of rules collection, which includes only rules with 'No Coupon' type or with validated coupon.
        // as a result, if the rule uses coupon code(s) ('Specific' or 'Auto' Coupon Type), it always contains validated coupon
        if ($rule->getCouponType() != 1) { // Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON           
           $address->setCouponCode($this->getCouponCode());
        }         
    }
    
    // CE 1.3 compatibility
    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0', '>='))
        {
            return parent::process($item);
        }
        
        // copying from base class, doing some modifications
        
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        $quote      = $item->getQuote();
        $address    = $this->_getAddress($item);

        //Clearing applied rule ids for quote and address
        if ($this->_isFirstTimeProcessRun !== true){
            $this->_isFirstTimeProcessRun = true;
            $quote->setAppliedRuleIds('');
            $address->setAppliedRuleIds('');
        }

        $itemPrice  = $item->getDiscountCalculationPrice();
        if ($itemPrice !== null) {
            $baseItemPrice = $item->getBaseDiscountCalculationPrice();
        } else {
            $itemPrice = $item->getCalculationPrice();
            $baseItemPrice = $item->getBaseCalculationPrice();
        }

        $appliedRuleIds = array();
        foreach ($this->_getRules() as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }
            $qty = $item->getTotalQty();
            $qty = $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
            $rulePercent = min(100, $rule->getDiscountAmount());

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            switch ($rule->getSimpleAction()) {
                case 'to_percent':
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                    //no break;
                case 'by_percent':
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $discountAmount    = ($qty*$itemPrice - $item->getDiscountAmount()) * $rulePercent/100;
                    $baseDiscountAmount= ($qty*$baseItemPrice - $item->getBaseDiscountAmount()) * $rulePercent/100;

                    if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                        $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                        $item->setDiscountPercent($discountPercent);
                    }
                    break;
                case 'to_fixed':
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*($itemPrice-$quoteAmount);
                    $baseDiscountAmount= $qty*($baseItemPrice-$rule->getDiscountAmount());
                    break;

                case 'by_fixed':
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $quoteAmount        = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount     = $qty*$quoteAmount;
                    $baseDiscountAmount = $qty*$rule->getDiscountAmount();
                    break;

                case 'cart_fixed':
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $quoteAmount        = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                        /**
                         * We can't use row total here because row total not include tax
                         */
                        $discountAmount     = min($itemPrice*$qty - $item->getDiscountAmount(), $quoteAmount);
                        $baseDiscountAmount = min($baseItemPrice*$qty - $item->getBaseDiscountAmount(), $cartRules[$rule->getId()]);
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;

                case 'buy_x_get_y':
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y>=$x) {
                        break;
                    }
                    $buy = 0; $free = 0;
                    while ($buy+$free<$qty) {
                        $buy += $x;
                        if ($buy+$free>=$qty) {
                            break;
                        }
                        $free += min($y, $qty-$buy-$free);
                        if ($buy+$free>=$qty) {
                            break;
                        }
                    }
                    $discountAmount    = $free*$itemPrice;
                    $baseDiscountAmount= $free*$baseItemPrice;
                    break;
            }

            $result = new Varien_Object(array(
                'discount_amount'      => $discountAmount,
                'base_discount_amount' => $baseDiscountAmount,
            ));
            Mage::dispatchEvent('salesrule_validator_process', array(
                'rule'    => $rule,
                'item'    => $item,
                'address' => $address,
                'quote'   => $quote,
                'qty'     => $qty,
                'result'  => $result,
            )); 

            $discountAmount = $result->getDiscountAmount();
            $baseDiscountAmount = $result->getBaseDiscountAmount();

            $percentKey = $item->getDiscountPercent();
            /**
             * Process "delta" rounding
             */
            if ($percentKey) {
                $delta      = isset($this->_roundingDeltas[$percentKey]) ? $this->_roundingDeltas[$percentKey] : 0;
                $baseDelta  = isset($this->_baseRoundingDeltas[$percentKey]) ? $this->_baseRoundingDeltas[$percentKey] : 0;
                $discountAmount+= $delta;
                $baseDiscountAmount+=$baseDelta;

                $this->_roundingDeltas[$percentKey]     = $discountAmount - $quote->getStore()->roundPrice($discountAmount);
                $this->_baseRoundingDeltas[$percentKey] = $baseDiscountAmount - $quote->getStore()->roundPrice($baseDiscountAmount);
                $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            } else {
                $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            }

            /**
             * We can't use row total here because row total not include tax
             * Discount can be applied on price included tax
             */
            $discountAmount     = min($item->getDiscountAmount()+$discountAmount, $itemPrice*$qty);
            $baseDiscountAmount = min($item->getBaseDiscountAmount()+$baseDiscountAmount, $baseItemPrice*$qty);

            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            // MODIFICATION BY AMASTY
            $currentCouponCode = explode(', ', $this->getCouponCode());
            if ($currentCouponCode && $rule->getCouponCode() && in_array($rule->getCouponCode(), $currentCouponCode))
            {
                $address->setCouponCode($this->getCouponCode());
            }
            // MODIFICATION BY AMASTY END

            if ($rule->getCouponCode() && ( strtolower($rule->getCouponCode()) == strtolower($this->getCouponCode()))) {
                $address->setCouponCode($this->getCouponCode());
            }
            $this->_addDiscountDescription($address, $rule);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        $item->setAppliedRuleIds(join(', ',$appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        return $this;
        
    }

    public function canProcessRule($rule, $address)
    {
        return parent::_canProcessRule($rule, $address);
    }
    
    // CE 1.3 compatibility
    public function processShippingAmount(Mage_Sales_Model_Quote_Address $address)
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0', '>='))
        {
            return parent::processShippingAmount($address);
        }
        
        $shippingAmount     = $address->getShippingAmountForDiscount();
        if ($shippingAmount!==null) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount     = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $quote              = $address->getQuote();
        $appliedRuleIds = array();
        foreach ($this->_getRules() as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            if (!$rule->getApplyToShipping() || !$this->_canProcessRule($rule, $address)) {
                continue;
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            $rulePercent = min(100, $rule->getDiscountAmount());
            switch ($rule->getSimpleAction()) {
                case 'to_percent':
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                case 'by_percent':
                    $discountAmount    = ($shippingAmount - $address->getShippingDiscountAmount()) * $rulePercent/100;
                    $baseDiscountAmount= ($baseShippingAmount - $address->getBaseShippingDiscountAmount()) * $rulePercent/100;
                    $discountPercent = min(100, $address->getShippingDiscountPercent()+$rulePercent);
                    $address->setShippingDiscountPercent($discountPercent);
                    break;
                case 'to_fixed':
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $shippingAmount-$quoteAmount;
                    $baseDiscountAmount= $baseShippingAmount-$rule->getDiscountAmount();
                    break;
                case 'by_fixed':
                    $quoteAmount        = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount     = $quoteAmount;
                    $baseDiscountAmount = $rule->getDiscountAmount();
                    break;

                case 'cart_fixed':
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $quoteAmount        = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                        $discountAmount     = min($shippingAmount-$address->getShippingDiscountAmount(), $quoteAmount);
                        $baseDiscountAmount = min($baseShippingAmount-$address->getBaseShippingDiscountAmount(), $cartRules[$rule->getId()]);
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;
            }

            $discountAmount     = min($address->getShippingDiscountAmount()+$discountAmount, $shippingAmount);
            $baseDiscountAmount = min($address->getBaseShippingDiscountAmount()+$baseDiscountAmount, $baseShippingAmount);
            $address->setShippingDiscountAmount($discountAmount);
            $address->setBaseShippingDiscountAmount($baseDiscountAmount);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            // MODIFICATION BY AMASTY
            $currentCouponCode = explode(', ', $this->getCouponCode());
            if ($currentCouponCode && $rule->getCouponCode() && in_array($rule->getCouponCode(), $currentCouponCode))
            {
                $address->setCouponCode($this->getCouponCode());
            }
            // MODIFICATION BY AMASTY END
            
            if ($rule->getCouponCode() && ( strtolower($rule->getCouponCode()) == strtolower($this->getCouponCode()))) {
                $address->setCouponCode($this->getCouponCode());
            }
            $this->_addDiscountDescription($address, $rule);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        return $this;
    }
    
}
