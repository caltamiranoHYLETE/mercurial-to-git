<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $moduleName the full module name, example Mage_Core
     * @return boolean
     */
    public function isModuleEnabled($moduleName = null)
    {
        if (!Mage::getConfig()->getNode('modules/' . $moduleName)) {
            return false;
        }

        $isActive = Mage::getConfig()->getNode('modules/' . $moduleName . '/active');
        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }
        return true;
    }

    public function isEnterpriseEdition()
    {
        return Mage::getConfig()->getNode('modules/Enterprise_Enterprise');
    }

    public function isVersionLessThan($major = 1, $minor = 4)
    {
        $curr = explode('.', Mage::getVersion()); // 1.3. compatibility
        $need = func_get_args();
        foreach ($need as $k => $v) {
            if ($curr[$k] != $v) {
                return ($curr[$k] < $v);
            }
        }
        return false;
    }

    /**
     * @deprecated
     * @param $code
     * @return bool
     */
    public function isModuleActive($code)
    {
        return ('true' == (string)Mage::getConfig()->getNode('modules/' . $code . '/active'));
    }

    public function getRewritesList()
    {
        $moduleFiles = glob(Mage::getBaseDir('etc') . DS . 'modules' . DS . '*.xml');

        if (!$moduleFiles) {
            return false;
        }

        // load file contents
        $unsortedConfig = new Varien_Simplexml_Config();
        $unsortedConfig->loadString('<config/>');
        $fileConfig = new Varien_Simplexml_Config();

        foreach ($moduleFiles as $filePath) {
            $fileConfig->loadFile($filePath);
            $unsortedConfig->extend($fileConfig);
        }

        // create sorted config [only active modules]
        $sortedConfig = new Varien_Simplexml_Config();
        $sortedConfig->loadString('<config><modules/></config>');

        foreach ($unsortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            if ('true' === (string)$moduleNode->active) {
                $sortedConfig->getNode('modules')->appendChild($moduleNode);
            }
        }

        $fileConfig = new Varien_Simplexml_Config();

        $_finalResult = array();

        foreach ($sortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            $codePool = (string)$moduleNode->codePool;
            $configPath = BP . DS . 'app' . DS . 'code' . DS . $codePool . DS . uc_words($moduleName, DS) . DS . 'etc' . DS . 'config.xml';

            $fileConfig->loadFile($configPath);

            $rewriteBlocks = array('blocks', 'models', 'helpers');

            foreach ($rewriteBlocks as $param) {
                if (!isset($_finalResult[$param])) {
                    $_finalResult[$param] = array();
                }

                if ($rewrites = $fileConfig->getXpath('global/' . $param . '/*/rewrite')) {
                    foreach ($rewrites as $rewrite) {
                        $parentElement = $rewrite->xpath('../..');
                        foreach ($parentElement[0] as $moduleKey => $moduleItems) {
                            $moduleItemsArray['rewrite'] = array();
                            $moduleItemsArray['codePool'] = array();
                            foreach ($moduleItems->rewrite as $rewriteLine) {
                                foreach ($rewriteLine as $key => $value) {
                                    $moduleItemsArray['rewrite'][$key] = (string)$value;
                                    $moduleItemsArray['codePool'][$key] = $codePool;
                                }
                            }
                            if ($moduleItems->rewrite) {
                                $_finalResult[$param] = array_merge_recursive(
                                    $_finalResult[$param],
                                    array($moduleKey => $moduleItemsArray)
                                );
                            }
                        }
                    }
                }
            }
        }

        return $_finalResult;
    }

    /**
     * Retrive possible conflicts list
     *
     * @return array
     */
    public function getPossibleConflictsList()
    {
        $_finalResult = $this->getRewritesList();

        foreach (array_keys($_finalResult) as $groupType) {

            foreach (array_keys($_finalResult[$groupType]) as $key) {
                // remove some repeating elements after merging all parents 
                foreach ($_finalResult[$groupType][$key]['rewrite'] as $key1 => $value) {
                    if (is_array($value)) {
                        $_finalResult[$groupType][$key]['rewrite'][$key1] = array_unique($value);
                    }

                    // if rewrites count < 2 - no conflicts - remove
                    if( 
                        (gettype($_finalResult[$groupType][$key]['rewrite'][$key1]) == 'array' && count($_finalResult[$groupType][$key]['rewrite'][$key1]) < 2) 
                        ||
                        gettype($_finalResult[$groupType][$key]['rewrite'][$key1]) == 'string'
                    ) {
                        unset($_finalResult[$groupType][$key]['rewrite'][$key1]);
                        unset($_finalResult[$groupType][$key]['codePool'][$key1]);
                    }
                }

                // clear empty elements
                if (count($_finalResult[$groupType][$key]['rewrite']) < 1) {
                    unset($_finalResult[$groupType][$key]);
                }


            }

            // clear empty elements
            if (count($_finalResult[$groupType]) < 1) {
                unset($_finalResult[$groupType]);
            }

        }

        return $_finalResult;
    }

    public function ajaxHtml()
    {
        return Mage::app()->getLayout()->createBlock('ambase/adminhtml_debug_general')->toHtml() .
            Mage::app()->getLayout()->createBlock('ambase/adminhtml_debug_conflict')->toHtml() .
            Mage::app()->getLayout()->createBlock('ambase/adminhtml_debug_rewrite')->toHtml() .
            Mage::app()->getLayout()->createBlock('ambase/adminhtml_debug_event')->toHtml();
    }

    public function getParentClasses($class)
    {
        $parents = @class_parents($class);
        $result = null;

        if ($parents !== false) {
            $result = array_values($parents);
        } 

        if (!$result || count($result) == 0) {
            $result = array('<span style="color:red">error occurred</span>');
        }
        
        return $result;
    }

    public function getEventsList()
    {
        $scopes = array(
            'global',
            'frontend',
            'adminhtml',
        );
        $collection = Mage::getResourceModel('ambase/event_collection');

        $data = array();
        foreach ($scopes as $scope) {
            $data = array_merge($data, $collection->_prepareData($scope));
        }

        return $data;
    }

    /**
     * @param $filename
     *
     * @return mixed
     */
    public static function sanitizeFileName($filename)
    {
        $chars = array(" ", '"', "'", "&", "/", "\\", "?", "#");

        // every forbidden character is replace by an underscore
        return str_replace($chars, '_', $filename);
    }
}
