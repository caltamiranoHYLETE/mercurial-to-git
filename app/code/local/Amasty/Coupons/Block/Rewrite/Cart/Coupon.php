<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_Block_Rewrite_Cart_Coupon extends Mage_Checkout_Block_Cart_Coupon
{
    protected function _toHtml()
    {
        $this->setTemplate('amcoupon/checkout/cart/coupon.phtml');
        return parent::_toHtml();
    }
    
    public function _getAppliedCoupons()
    {
        return $this->getQuote()->_getAppliedCoupons();
    }
}