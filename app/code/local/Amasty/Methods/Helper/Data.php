<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Helper_Data extends Mage_Core_Helper_Abstract
{
    const VAR_RESTRICT_METHOD = 'restrict_method';
    static protected $_customerGroupId = null;
    
    public function canUseMethod($method, $type, $customer = null)
    {
        if ('payment' == $type)
        {
            return $this->_canUsePaymentMethod($method, $customer);
        }
        if ('shipping' == $type)
        {
            return $this->_canUseShippingMethod($method);
        }
        return true;
    }
    
    protected function _getCustomerGroupId($customer = null)
    {
        if (!is_null(self::$_customerGroupId))
        {
            return self::$_customerGroupId;
        }
        if (!$customer) {
            $customer = Mage::helper('customer')->getCustomer();
        }

        if (!$customer->getId() && $this->_doReturnNullForNotLoggedIn())
        {
            return 0;
        }

        return $customer->getGroupId();
    }

    /**
     * @param $type
     * @return bool
     */
    public function isNullGroups($type)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $collection = Mage::getModel('ammethods/visibility')->getCollection();
        $general = $collection
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('group_ids', array("neq" => ""))
        ->count();
        return $general;
    }
    /**
    * @param string $method Method Code
    */
    protected function _canUseShippingMethod($method)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $visibility = Mage::getModel('ammethods/visibility')->loadVisibility('shipping', $websiteId, $method);
        if (!$visibility->getEntityId())
        {
            // if no record, will allow
            return true;
        }
        if ('' === $visibility->getGroupIds())
        {
            return $this->isRestrictShippingMethod();
        }
        $allowedGroups = explode(',', $visibility->getGroupIds());

        $customer = $this->_getAdminCustomer();
        $customerGroupId = $this->_getCustomerGroupId($customer);

        if (empty($customerGroupId)) {
            $customerGroupId = 0;
        }
        if ($this->isRestrictShippingMethod()){
            if (in_array($customerGroupId, $allowedGroups) === false)
            {
                return true;
            }
        } else {
            if (in_array($customerGroupId, $allowedGroups) === true)
            {
                return true;
            }
        }

        return false;
    }
    
    /**
    * @param object $method Method Object
    */
    protected function _canUsePaymentMethod($method, $customer = null)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $visibility = Mage::getModel('ammethods/visibility')->loadVisibility('payment', $websiteId, $method->getCode());

        if (!$visibility->getEntityId())
        {

            // if no record, will allow
            return true;
        }

        if ('' === $visibility->getGroupIds())
        {
            return $this->isRestrictPaymentMethod();
        }

        $allowedGroups = explode(',', $visibility->getGroupIds());
        if ($this->isRestrictPaymentMethod()) {
            if (in_array($this->_getCustomerGroupId($customer), $allowedGroups) === false) {
                return true;
            }
        } else {
            if (in_array($this->_getCustomerGroupId($customer), $allowedGroups) === true) {
                return true;
            }
        }

        return false;
    }
    
    protected function _doReturnNullForNotLoggedIn()
    {
        $onepage = Mage::getSingleton('checkout/type_onepage');
        if (Mage_Checkout_Model_Type_Onepage::METHOD_GUEST == $onepage->getQuote()->getCheckoutMethod())
        {
            return true;
        }
        return false;
    }

    public function isRestrictShippingMethod()
    {
        return (int)Mage::getStoreConfig('ammethods/shipping/' . self::VAR_RESTRICT_METHOD) === 1;
    }

    public function isRestrictPaymentMethod()
    {
        return (int)Mage::getStoreConfig('ammethods/payment/' . self::VAR_RESTRICT_METHOD) === 1;
    }

    protected function _getAdminCustomer()
    {
        $sessionAdmin = Mage::getSingleton('adminhtml/session_quote');
        if ($sessionAdmin->getQuote()->getId()) {
            return $sessionAdmin->getCustomer();
        }

        return null;
    }
}