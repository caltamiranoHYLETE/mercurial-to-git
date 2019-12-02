<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */


class Amasty_Coupons_Model_Observer 
{
    public function handleSalesOrderPlaceAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return $this;
        }
        // default handler works well with 1 code, we don't need to hnabge anything
        if (strpos($order->getCouponCode(), ', ') === false) {
            return $this;
        }
        $customerId = $order->getCustomerId();

        if (version_compare(Mage::getVersion(), '1.4.1.0', '>='))
        {    
            $coupon = Mage::getModel('salesrule/coupon');
            foreach (explode(', ', $order->getCouponCode()) as $code){
                $coupon->load($code, 'code');
                if ($coupon->getId()) {
                    $coupon->setTimesUsed($coupon->getTimesUsed() + 1);
                    $coupon->save();
                    if ($customerId) {
                        $couponUsage = Mage::getResourceModel('salesrule/coupon_usage');
                        $couponUsage->updateCustomerCouponTimesUsed($customerId, $coupon->getId());
                    }
                }            
            }
        }      
 
        return $this;
    }
    
    public function handleSalesruleValidatorProcess($observer) 
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0') < 0)
            return $this;               
        $codes  = $observer->getEvent()->getQuote()->getCouponCode();
        if (!$codes)
            return $this;
            
        $codes = explode(', ', $codes);

        if (count($codes) < 2 )
            return $this;
            
        if (!(Mage::getStoreConfig('amcoupons/codes/allow_same_rule')))
            return $this;
                
        $cntPerRule = $observer->getEvent()->getQuote()->getCouponPerRuleCount();
        if (!$cntPerRule){
            // calculate once
            $cntPerRule = $this->_calculateCouponPerRuleCount($codes);
            $observer->getEvent()->getQuote()->setCouponPerRuleCount($cntPerRule);
        }
        $ruleId = $observer->getEvent()->getRule()->getId();

        if (isset($cntPerRule[$ruleId])){
            $result = $observer->getEvent()->getResult();
            $result->setDiscountAmount($result->getDiscountAmount()*$cntPerRule[$ruleId]);
            $result->setBaseDiscountAmount($result->getBaseDiscountAmount()*$cntPerRule[$ruleId]);
        }    
        
        return $this;
    }
    
     protected function _calculateCouponPerRuleCount($codes)
    {
        $collection = Mage::getResourceModel('salesrule/coupon_collection');
        $select = $collection->getSelect();
        $select
            ->reset(Zend_Db_Select::COLUMNS)
            ->from('', array('rule_id', 'cnt'=> new Zend_Db_Expr('COUNT(*)')))
            ->where('code IN(?)', $codes)
            ->group('rule_id');
            
        // we can't use collection    
        $db = Mage::getSingleton('core/resource')->getConnection('amcoupons_write');
        $rows = $db->fetchPairs($select);
        
        return $rows;
    }   

    /**
     * PGL (petr.glu@hotmail.com) 
     * @return bool
     */
    protected function _allowSameRule($observer)
    {
        $rule = $observer->getEvent()->getRule();
        return (Mage::getStoreConfig('amcoupons/codes/allow_same_rule') || $rule->getAllowMultipleCoupons(0));
    }
    
}

