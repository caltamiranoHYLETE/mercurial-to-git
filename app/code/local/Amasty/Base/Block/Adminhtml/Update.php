<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Block_Adminhtml_Update extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_moduleHelper;

    protected function _getModuleHelper()
    {
        if (!$this->_moduleHelper) {
            $controllerModule = Mage::app()->getRequest()->getControllerModule();
            $this->_moduleHelper = Mage::helper("ambase/module")->init($controllerModule);
        }

        return $this->_moduleHelper;
    }

    public function isNewVersionAvailable()
    {
        return $this->isSubscribed() && $this->_getModuleHelper()->isNewVersionAvailable();
    }

    public function getModuleTitle()
    {
        return $this->_getModuleHelper()->getModuleTitle();
    }

    public function getModuleLink()
    {
        return $this->_getModuleHelper()->getModuleLink();
    }

    public function getModuleCode()
    {
        return $this->_getModuleHelper()->getModuleCode();
    }

    public function getLatestVersion()
    {
        return $this->_getModuleHelper()->getLatestVersion();
    }

    public function getCloseUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/ambase_base/closeUpdate", array(
            'code' => $this->getModuleCode()
        ));
    }

    public function getUnsubscribeUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/system_config/edit/section/ambase", array(
        
        ));
    }

    public function isSubscribed()
    {
        return $this->_getModuleHelper()->isSubscribed();
    }
}
