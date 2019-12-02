<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_Model_SalesRule_Mysql4_Rule_Collection extends Mage_SalesRule_Model_Mysql4_Rule_Collection
{
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode='', $now=null)
    {

        if (version_compare(Mage::getVersion(), '1.4.1.0', '>='))
        {
            $this->_setValidationFilter141x($websiteId, $customerGroupId, $couponCode, $now);
        } else 
        {
            $this->_setValidationFilter140x($websiteId, $customerGroupId, $couponCode, $now);
        }
        return $this;
    }
    
    protected function _setValidationFilter140x($websiteId, $customerGroupId, $couponCode='', $now=null)
    {
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }
        
        $coupons = explode(', ', $couponCode);
        if (is_array($coupons) && 1 == count($coupons))
        {
            $coupons = $couponCode;
        }

        $this->getSelect()->where('is_active=1');
        $this->getSelect()->where('find_in_set(?, website_ids)', (int)$websiteId);
        $this->getSelect()->where('find_in_set(?, customer_group_ids)', (int)$customerGroupId);
        if (!$coupons) {
            $this->getSelect()->where("coupon_code is null or coupon_code=''");
        }
        else {
            if (is_array($coupons))
            {
                foreach ($coupons as $coupon)
                {
                    $couponsForIn[] = "'" . $coupon . "'";
                }
                $this->getSelect()->where("coupon_code is null or coupon_code='' or coupon_code IN (" . implode(', ', $couponsForIn) . ")");
            } else 
            {
                $this->getSelect()->where("coupon_code is null or coupon_code='' or coupon_code='$coupons'");
            }
        }
        $this->getSelect()->where('from_date is null or from_date<=?', $now);
        $this->getSelect()->where('to_date is null or to_date>=?', $now);
        $this->getSelect()->order('sort_order');
        return $this;
    }
    
    protected function _setValidationFilter141x($websiteId, $customerGroupId, $couponCode='', $now=null)
    {
        if ($this->getFlag('validation_filter')) {
            return $this;
        }        
        
        if ($couponCode && !is_array($couponCode))
            $couponCode = explode(', ', $couponCode); // multiple coupon compatibility
            
              
        $this->getSelect()->reset();
        $this->getSelect()->from(array('main_table' => $this->getTable('salesrule/rule')));        

        $this->addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now);

        if ($couponCode) {
            $this->getSelect()->joinLeft(array('c' => $this->getTable('salesrule/coupon')), 
                'main_table.rule_id = c.rule_id ', 
                array('code')
            ); 
            // Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON is not defined
            $this->getSelect()->where(
                $this->getSelect()->getAdapter()->quoteInto(' main_table.coupon_type = ?', 1)
                .
                $this->getSelect()->getAdapter()->quoteInto(' OR c.code IN(?)', $couponCode)
            );
            
            $this->getSelect()->group('main_table.rule_id');
        } 
        else {
            $this->getSelect()->where('main_table.coupon_type = ?', 1);
        }
        
        $this->getSelect()->order('sort_order'); 
        
        $this->setFlag('validation_filter', true);
        
        return $this;
    }
    
    public function addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now = null)
    {
        
        if (method_exists('Mage', 'getEdition')){ // available from CE 1.7
            return parent::addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now); 
        }
         
        $this->getSelect()->where('is_active=1');
        $this->getSelect()->where('find_in_set(?, website_ids)', (int)$websiteId);
        $this->getSelect()->where('find_in_set(?, customer_group_ids)', (int)$customerGroupId); 
        
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }        
        $this->getSelect()->where('from_date is null or from_date<=?', $now);
        $this->getSelect()->where('to_date is null or to_date>=?', $now);
               
        return $this;
    }
}
