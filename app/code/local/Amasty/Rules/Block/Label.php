<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Block_Label extends Amasty_Rules_Block_Banner
{
    function getImage(Mage_SalesRule_Model_Rule $validRule = null)
    {
        $validRule = $this->_getValidRule();
        $labelImage = $validRule->getData('label_img');

        return Mage::helper("amrules/image")->getLink($labelImage);
    }

    function getAlt(Mage_SalesRule_Model_Rule $validRule = null)
    {
        $validRule = $this->_getValidRule();

        return $validRule->getData('label_alt');
    }
}
