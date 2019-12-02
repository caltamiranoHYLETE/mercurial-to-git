<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Block_Label extends Amasty_PromoBannersLite_Block_Banner
{
    /**
     * @param Mage_SalesRule_Model_Rule|null $validRule
     * @return mixed
     */
    public function getImage(Mage_SalesRule_Model_Rule $validRule = null)
    {
        $validRule = $this->_getValidRule();

        $file = $validRule->getData('ambannerslite_label_img');

        return $file ? Mage::helper("ambannerslite/image")->getLink($file) : null;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        $validRule = $this->_getValidRule();

        return $validRule->getData('ambannerslite_label_alt');
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        $validRule = $this->_getValidRule();

        return $validRule->getData('ambannerslite_label_enable');
    }
}
