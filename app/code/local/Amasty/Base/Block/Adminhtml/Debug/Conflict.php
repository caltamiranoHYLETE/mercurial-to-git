<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Block_Adminhtml_Debug_Conflict extends Amasty_Base_Block_Adminhtml_Debug_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ambase/debug/conflict.phtml');
    }

    public function getPossibleConflictsList()
    {
        return Mage::helper("ambase")->getPossibleConflictsList();
    }

    public function getFixUrl($object, $module, $rewrite)
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/ambase_base/fix", array(
            "object" => $object,
            "module" => $module,
            "rewrite" => $rewrite
        ));
    }

    public function getRollbackUrl($object, $module, $rewrite)
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/ambase_base/rollback", array(
            "object" => $object,
            "module" => $module,
            "rewrite" => $rewrite
        ));
    }

    public function hasConflict($rewrites)
    {
        $ret = false;
        foreach ($rewrites as $rewrite) {
            if (strpos($rewrite, "Amasty") === false) {
                $ret = true;
                break;
            }
        }

        return $ret;
    }

    public function conflictResolved($codePool, $rewrites)
    {
        $ret = false;
        krsort($rewrites);

        $extendsClasses = $rewrites;

        foreach ($rewrites as $rewriteIndex => $class) {
            unset($extendsClasses[$rewriteIndex]);

            if (count($extendsClasses) > 0) {
                $classPath = $this->getClassPath($rewrites, $codePool, $rewriteIndex);
                $pureClassName = Amasty_Base_Model_Conflict::getPureClassName($class);

                $lines = file($classPath);
                foreach ($lines as $line) {
                    if (strpos($line, $pureClassName) !== false) {
                        $ret = true;
                        break;
                    }
                }
            }
        }

        return $ret;
    }
}
