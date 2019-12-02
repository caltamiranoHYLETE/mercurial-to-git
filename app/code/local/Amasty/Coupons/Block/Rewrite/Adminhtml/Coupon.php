<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */


class Amasty_Coupons_Block_Rewrite_Adminhtml_Coupon extends Mage_Adminhtml_Block_Sales_Order_Create_Coupons
{
    protected function _toHtml()
    {
        $this->setTemplate('amasty/amcoupon/coupon.phtml');
        return parent::_toHtml();
    }

    public function getCouponCodes($withoutThisCoupon = NULL, $toString = false)
    {
        $couponCodes = explode(', ', $this->getCouponCode());

        if (!is_null($withoutThisCoupon)) {
            if(($key = array_search($withoutThisCoupon, $couponCodes)) !== false) {
                unset($couponCodes[$key]);
            }
        }

        if ($toString) {
            $couponCodes = implode(', ', $couponCodes);
        }

        return $couponCodes;
    }
}
