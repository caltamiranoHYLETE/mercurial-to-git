<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Helper_Utils extends Mage_Core_Helper_Abstract
{
    public function _exit($code = 0)
    {
        $exit = create_function('$a', 'exit($a);');
        $exit($code);
    }

    public function _echo($a)
    {
        $echo = create_function('$a', 'echo $a;');
        $echo($a);
    }
}
