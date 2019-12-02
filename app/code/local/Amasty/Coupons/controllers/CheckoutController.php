<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_CheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
    
    public function cancelCouponAction()
    {
        $codeToCancel = $this->getRequest()->getParam('amcoupon_code_cancel');
        $isAjax = $this->getRequest()->getParam('isAjax');
        
        $appliedCoupons = $this->_getQuote()->_getAppliedCoupons();
        
        foreach ($appliedCoupons as $i => $coupon)
        {
            if ($coupon == $codeToCancel)
            {
                unset($appliedCoupons[$i]);
                try
                {
                    if ($this->_getQuote()->setCouponCode(implode(', ', $appliedCoupons))->save())
                    {
                    	// MYLES: EDIT TO ANOTHER DEV'S MODULE; REWRITE!
						Mage::dispatchEvent("mediotype_coupon_removal_check", ['quote' => $this->_getQuote(), 'couponCodeRemoved' => $codeToCancel]);

                        $this->_getSession()->addSuccess($this->__('Coupon code %s was canceled.', $codeToCancel));
                    }
                }
                catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($this->__('Cannot cancel the coupon code.'));
                }
                break;
            }
        }
        
        if ($isAjax && Mage::helper("amcoupons")->isAmastyOnestepcheckoutInstalled()){
            $this->_redirect('amscheckoutfront/onepage/cancelCoupon', array('_secure' => true));
        } else {
            $this->_redirectReferer('*/*');
        }

        return $this;
    }
}
