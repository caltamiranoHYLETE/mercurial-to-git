<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected function _getPath()
    {
        return Mage::getBaseDir('media') . DS . 'amrules' . DS;
    }

    function upload($field)
    {
        $fileName = null;
        try {
            $uploader = new Varien_File_Uploader($field);
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setFilesDispersion(false);
            $uploader->setAllowRenameFiles(false);

            $path = $_FILES[$field]['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $fileName = uniqid($field . "_") . "." . $ext;
            $uploader->save($this->_getPath(), $fileName);
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $fileName;
    }

    function getLink($file)
    {
        $baseDir = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)  . 'amrules' . DS . $file;
        $result = $file ? $baseDir : null;

        return $result;
    }
}