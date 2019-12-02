<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */


require_once 'Mage/Checkout/controllers/CartController.php';

class Amasty_Coupons_CartController extends Mage_Checkout_CartController
{
    public function indexAction()
    {
        $cartCoupons = $this->_getCart()->getQuote()->getCouponCode();
        $cartCouponsCount = 0;
        if (!is_null($cartCoupons)) {
            $cartCouponsCount = count(explode(', ', $this->_getCart()->getQuote()->getCouponCode()));
        }
        $maxCouponsCount = Mage::getStoreConfig('amcoupons/codes/max_num_coupons');

        if (($maxCouponsCount != 0) && ($cartCouponsCount >= $maxCouponsCount)) {
            Mage::getSingleton('core/session')->addNotice('You have entered the maximum number of allowed coupons. If you wish to enter another, please delete one first');
        }

        parent::indexAction();
    }
}
