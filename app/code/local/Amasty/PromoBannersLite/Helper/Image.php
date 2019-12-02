<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */
class Amasty_PromoBannersLite_Helper_Image extends Mage_Core_Helper_Abstract
{
    /**
     * return media path
     *
     * @return string
     */
    protected function _getPath()
    {
        return Mage::getBaseDir('media') . DS . 'ambannerslite' . DS;
    }

    /**
     * @param $field
     * @return null|string
     */
    public function upload($field)
    {
        $result= null;
        try {
            $uploader = new Varien_File_Uploader($field);
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'svg'));
            $uploader->setFilesDispersion(false);
            $uploader->setAllowRenameFiles(false);

            $path = $_FILES[$field]['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $fileName = uniqid($field . "_") . "." . $ext;
            $result = $uploader->save($this->_getPath(), $fileName);
            $result = $result['file'];
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $result;
    }

    /**
     * Get Link for file
     * 
     * @param $file
     * @return null|string
     */
    public function getLink($file)
    {
        return $file ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'ambannerslite' . DS . $file : null;
    }
}
