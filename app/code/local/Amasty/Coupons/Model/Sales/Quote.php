<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_Model_Sales_Quote extends Mage_Sales_Model_Quote
{
    protected $_coupons = NULL;
    protected $_newCodes = NULL;

    /**
     * @string $inputCodes comma separated list of coupon codes
     * Override the default setter
     * Add 1 or multiple coupons  to existing on "add" action OR replace existing coupons on "remove" action
     */
    public function setCouponCode($newCodes)
    {
        $this->_newCodes = $newCodes;
        $newCodesArray = array();
        if (!is_array($newCodes)) {
            $newCodesArray = explode(', ', $newCodes);
        }

        $appliedCodes = $this->getCouponCode(true);

        if (!$appliedCodes) {
            $appliedCodesArray = array();
        } else {
            $appliedCodesArray = explode(', ', $appliedCodes);
        }

        $resultCodesArray = $this->_handleCouponMethod($newCodesArray, $appliedCodesArray);
        $resultCodes = NULL;

        $coupons = $this->_loadCoupons($resultCodesArray);
        $couponsCount = 0;
        if ($coupons) {
            $coupons = $this->_removeNonActiveCoupons($coupons);
            $coupons = $this->_handlingSameRule($coupons);
            $coupons = $this->_removeAfterStop($coupons);
            $coupons = $this->_addUniqueCode($coupons);
            $coupons = $this->_addAllowedCodes($coupons, $newCodesArray);

            $couponsCount = count($coupons);
            $resultCodes = $this->_convertCouponesToCodesString($coupons, $newCodes);
        }

        $maxCouponsCount = Mage::getStoreConfig('amcoupons/codes/max_num_coupons');
        if (($maxCouponsCount == 0) || ($couponsCount <= $maxCouponsCount)) {
            $this->setData('coupon_code', $resultCodes);
        }

        return $this;
    }

    /*
    * Override the default getter
    */
    public function getCouponCode($all = false)
    {
        $backtrace = debug_backtrace();
        $loadBlock = 'noblock';
        if ($backtrace[1]['function'] == '_processActionData' && $backtrace[1]['class'] == 'Mage_Adminhtml_Sales_Order_CreateController') {
            $loadBlock = 'loadBlock';
        }
        $backtrace = NULL;
        if (in_array(Mage::app()->getRequest()->getActionName(), array('couponPost', 'add_coupon', $loadBlock))) {
            if ($all) {
                return parent::getData('coupon_code');
            }

            $resultCodes = parent::getData('coupon_code');
            if (!empty($resultCodes)) {
                $inputCode = Mage::app()->getRequest()->getParam('coupon_code');
                if (is_null($inputCode)) {
                    $order = Mage::app()->getRequest()->getParam('order');
                    if (is_array($order)
                        && array_key_exists('coupon', $order)
                        && is_array($order['coupon'])
                        && array_key_exists('code', $order['coupon'])
                    ) {
                        $inputCode = $order['coupon']['code'];
                    }
                }
                if (is_null($inputCode)) {
                    $data = Mage::app()->getRequest()->getPost('order');
                    if (isset($data['coupon']['code'])) {

                        $inputCode = trim($data['coupon']['code']);
                    }
                }

                $resultCodesArray = explode(', ', $resultCodes);
                $resultCodesArray = array_map('strtolower', $resultCodesArray);
                foreach ($resultCodesArray as $element) {
                    if (strcasecmp($inputCode, $element) >= 0) {
                        return $inputCode;
                    }
                }
            }
        }

        // return last for right validation
        return parent::getData('coupon_code');
    }

    public function _getAppliedCoupons()
    {
        $codesArray = array();
        $codes = $this->getCouponCode(true);
        if ($codes) {
            $codesArray = explode(', ', $codes);
        }

        return array_filter($codesArray);
    }

    protected function _addAllowedCodes($coupons, $newCodesArray)
    {
        $allowedCodes = Mage::getStoreConfig('amcoupons/codes/allowed_codes', $this->getStoreId());
        $allowedCodes = trim($allowedCodes);
        $isAllowedCode = false;

        if ((count($coupons) > 1) && $allowedCodes) {
            $allowedCodes = strtolower($allowedCodes);
            $allowedCodes = str_replace(' ', '', $allowedCodes);
            $allowedCodes = explode(',', $allowedCodes);

            if (isset($coupons)) {
                foreach ($coupons as $currentCoupon) {
                    if (in_array(strtolower($currentCoupon['code']), $allowedCodes)) {
                        $isAllowedCode = true;
                    }
                }
            }

            if (!$isAllowedCode) {
                foreach ($coupons as $key => $currentCoupon) {
                    if (in_array($currentCoupon['code'], $newCodesArray)) {
                        unset($coupons[$key]);
                    }
                }
            }
        }

        return $coupons;
    }

    protected function _loadCoupons($allCodes)
    {
        if (!empty($allCodes)) {
            if (!isset($this->_coupons)) {
                $db = Mage::getSingleton('core/resource')->getConnection('core_read');
                $select = $db->select()
                    ->from(array('main_table' => Mage::getSingleton('core/resource')->getTableName('salesrule/rule')))
                    ->joinLeft(array('r' => Mage::getSingleton('core/resource')->getTableName('salesrule/coupon')), 'main_table.rule_id = r.rule_id', array('code'))
                    ->order('main_table.sort_order');

                foreach ($allCodes as $code) {
                    $select->orWhere('r.code = ?', $code);
                }

                $this->_coupons = $db->fetchAll($select);
            }
        } else {
            $this->_coupons = array();
        }

        return $this->_coupons;
    }

    protected function _handleCouponMethod($newCodesArray, $appliedCodesArray)
    {
        if (!$this->_isActionNameIn(array('indexAction')) || Mage::getSingleton('core/session')->getAmcouponsNumber() === 0) {//if first time check logic
            if ($this->_isAddNewCoupon()
                || $this->_isMerge()
                || $this->_isActionNameIn(array('_updateShoppingCart', 'deleteAction'))
                || $this->_isActionNameIn(array('_setCartCouponCode'), 'Mage_Checkout_Model_Type_Onepage')
            ) {
                $resultCodesArray = array_merge($newCodesArray, $appliedCodesArray);
                Mage::getSingleton('core/session')->setAmcouponsAction('apply');
            } else {
                $resultCodesArray = $this->_handleRemovalCouponsMethod($newCodesArray, $appliedCodesArray);
                Mage::getSingleton('core/session')->setAmcouponsAction('remove');
            }
            Mage::getSingleton('core/session')->setAmcouponsNumber(1);
        } else {//if second time - do the same like in first time(because after estimating of shipping address(second time) we can't understand what to do
            if (Mage::getSingleton('core/session')->getAmcouponsAction() == 'apply') {
                $resultCodesArray = array_merge($newCodesArray, $appliedCodesArray);
            } else {
                $resultCodesArray = $appliedCodesArray;
            }
            Mage::getSingleton('core/session')->setAmcouponsNumber(0);
        }


        return $resultCodesArray;
    }

    protected function _handleRemovalCouponsMethod($newCodesArray, $appliedCodesArray)
    {
        $resultCodesArray = array();

        foreach ($appliedCodesArray as $k => $appliedCode) {
            if (!in_array($appliedCode, $newCodesArray)) {
                unset($appliedCodesArray[$k]);
            }
            $resultCodesArray = $appliedCodesArray;
        }

        return $resultCodesArray;
    }

    protected function _isMerge()
    {
        $isMerge = false;
        $backTrace = debug_backtrace();
        foreach ($backTrace as $step) {
            if (isset($step['object']) && ($step['object'] instanceof Mage_Sales_Model_Quote) && ($step['function'] == 'merge')) {
                $isMerge = true;
                break;
            }

            // START HYL-56 CUSTOMIZATION
            elseif (isset($step['object']) && $step['object'] instanceof Mage_Checkout_Model_Cart_Coupon_Api) {
                $isMerge = true;
                break;
            }
            // END HYL-56 CUSTOMIZATION
        }
        $backTrace = NULL;
        return $isMerge;
    }

    protected function _removeSameRuleCoupons($coupons)
    {
        $rulesIds = array();
        foreach ($coupons as $k => $coupon) {
            // PGL (petr.glu@hotmail.com) - check rule configuration for allowing multiple coupons
            if (in_array($coupon['rule_id'], $rulesIds) && $coupon['allow_multiple_coupons'] != 1) {
                unset($coupons[$k]);
            }
            $rulesIds[] = $coupon['rule_id'];
        }

        return $coupons;
    }

    protected function _handlingSameRule($coupons)
    {
        $isAllow = Mage::getStoreConfig('amcoupons/codes/allow_same_rule');
        if (!$isAllow) {
            $coupons = $this->_removeSameRuleCoupons($coupons);
        }

        return $coupons;
    }

    /**
     * If found unique code - other codes will be deleted
     * @param $coupons
     * @return array|bool
     */
    protected function _addUniqueCode($coupons)
    {
        if (isset($coupons)) {
            $uniqueCodesArray = explode(",", str_replace(array(' ', ';'), '', Mage::getStoreConfig('amcoupons/codes/code', $this->getStoreId())));
            foreach ($coupons as $currentCoupone) {
                if (in_array($currentCoupone['code'], $uniqueCodesArray)) {
                    return array($currentCoupone);
                }
            }
        }

        return $coupons;
    }

    protected function _convertCouponesToCodesString($coupons, $newCodes)
    {

        $newCodesWithoutSpaces = trim($newCodes);
        $codesArray = array();
        foreach ($coupons as $coupon) {
            if ($coupon['code'] == $newCodesWithoutSpaces) {
                $codesArray[] = $newCodes;
            } else {
                $codesArray[] = $coupon['code'];
            }
        }
        $codes = implode(', ', array_filter($codesArray));

        return $codes;
    }

    protected function _removeAfterStop($coupons)
    {
        $beforeStopCoupons = array();
        foreach ($coupons as $coupon) {
            $beforeStopCoupons[] = $coupon;
            if ($coupon['stop_rules_processing'] == 1) {

                //same rules
                $isAllow = Mage::getStoreConfig('amcoupons/codes/allow_same_rule');
                if ($isAllow) {
                    $sameRulesCoupons = $this->_getSameRulesCoupons($coupon, $coupons);
                    $beforeStopCoupons = array_merge($beforeStopCoupons, $sameRulesCoupons);
                }
                break;
            }
        }

        return $beforeStopCoupons;
    }

    protected function _getSameRulesCoupons($stopCoupon, $coupons)
    {
        $sameRulesCoupons = array();
        foreach ($coupons as $k => $coupon) {
            if (($stopCoupon['code'] != $coupon['code']) && ($stopCoupon['rule_id'] == $coupon['rule_id'])) {
                $sameRulesCoupons[] = $coupon;
            }
        }

        return $sameRulesCoupons;
    }

    protected function _removeNonActiveCoupons($coupons)
    {
        foreach ($coupons as $k => $coupon) {
            if ($coupon['is_active'] == 0) {
                unset($coupons[$k]);
            }
        }

        return $coupons;
    }

    /**
     * When adding the code from the default form we always have the "remove" param
     * when deleting the code we have no any params as we are using different from
     * @return bool
     */
    protected function _isAddNewCoupon()
    {
        $isRemove = null;
        if (Mage::app()->getStore()->isAdmin()) {
            $request = Mage::app()->getRequest()->getParam('order');
            $code = $request['coupon']['code'];
            if (!empty($code)) {
                $isRemove = 0;
            }
        }

        if (!isset($isRemove)) {
            $isRemove = Mage::app()->getRequest()->getParam('remove');
            if (!isset($isRemove) && $this->_isXcoupon()) {
                $isRemove = 0;
            }
        }

        return (isset($isRemove) && $isRemove == 0);
    }

    protected function _isXcoupon()
    {
        $isXcoupon = false;
        $backTrace = debug_backtrace();
        foreach ($backTrace as $step) {
            if (isset($step['object']) && ($step['object'] instanceof Amasty_Xcoupon_Model_Observer)) {
                $isXcoupon = true;
                break;
            }
        }

        $backTrace = null;

        return $isXcoupon;
    }

    protected function _validateCouponCode()
    {
        $codes = $this->_getAppliedCoupons();
//        $validCodes = array();
//        $validatorModel = Mage::getModel('salesrule/validator');
//        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
//        $websiteId = Mage::getModel('core/store')->load($quote->getStoreId())->getWebsiteId();
//        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
//        $availableRules = Mage::getResourceModel('salesrule/rule_collection')
//            ->setValidationFilter($websiteId, $customerGroupId, $codes)
//            ->load();
//        $availableRulesIds = array();
//        foreach ($availableRules as $rule) {
//            $availableRulesIds[] = $rule->getId();
//        }

//        foreach ($codes as $code) {
//            $isValid = false;
//            $coupon = Mage::getModel('salesrule/coupon')->load($code, 'code');
//            $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
//            if (in_array($rule->getId(), $availableRulesIds)) {
//                $addresses = $this->getAllAddresses();
//                if (count($addresses)>0) {
//                    foreach ($addresses as $address) {
//                        $isValid = $validatorModel->canProcessRule($rule, $address);
//                    }
//                }
//            }
//
//            if ($isValid) {
//                $validCodes[] = $code;
//            }
//        }
//        $validCodesString = implode(', ', $validCodes);
//        $this->setData('coupon_code', '');
//        $this->setData('coupon_code', $validCodesString);

        if ($codes) {
            $addressHasCoupon = false;
            $addresses = $this->getAllAddresses();
            if (count($addresses) > 0) {
                foreach ($addresses as $address) {
                    if ($address->hasCouponCode()) {
                        $addressHasCoupon = true;
                    }
                }
                if (!$addressHasCoupon) {
                    $strtolower = function($value) {
                        return strtolower($value);
                    };
                    $coupons = explode(', ', $this->getCouponCode(true));
                    $couponsToLower = array_map($strtolower, $coupons);
                    if (count($coupons) && !$this->_isRemoveCoupons()) {
                        //delete not valid coupon
                        $newCode = is_array($this->_newCodes) ? null : $this->_newCodes;
                        if (($key = array_search(strtolower($newCode), $couponsToLower)) !== FALSE) {
                            unset($coupons[$key]);
                        }
                        $coupons = implode(', ', $coupons);
                        $this->setData('coupon_code', $coupons);
                    } else {
                        $this->setData('coupon_code', '');
                    }
                }
            }
        }
        return $this;
    }

    protected function _isRemoveCoupons()
    {
        $backTrace = debug_backtrace();
        foreach ($backTrace as $step) {
            if (isset($step['class']) && isset($step['function']) && $step['class'] == 'Mage_Checkout_CartController' &&
                ($step['function'] == '_emptyShoppingCart' || ($step['function'] == 'deleteAction' && $this->getItemsQty() == 0))
            ) {
                return true;
            }
        }
        $backTrace = null;
        return false;
    }

    protected function _isActionNameIn(array $actions, $controller = 'Mage_Checkout_CartController')
    {
        $isAction = false;
        $backTrace = debug_backtrace();
        foreach ($backTrace as $step) {
            if (isset($step['object']) && (is_a($step['object'], $controller)) && in_array($step['function'], $actions)) {
                $isAction = true;
                break;
            }
        }
        $backTrace = NULL;
        return $isAction;
    }
}
