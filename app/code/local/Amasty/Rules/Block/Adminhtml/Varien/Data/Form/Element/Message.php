<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Block_Adminhtml_Varien_Data_Form_Element_Message extends Varien_Data_Form_Element_Note
{
    public function getElementHtml()
    {
        $html = '<div class="message" id="' . $this->getHtmlId() . '">';
        if (Mage::helper('core')->isModuleEnabled('Amasty_Banners')) {
            $urlToBanners = Mage::helper("adminhtml")->getUrl('adminhtml/ambanners_rule/index');
            $message = Mage::helper('amrules')
                ->__('To work with advanced banners go to <a href="%s" target="_blank">Promo Banners</a>.',
                $urlToBanners);
        } else {
            $message = Mage::helper('amrules')
                ->__('In order to use advanced banners functionality, please use <a href="%s" target="_blank">Promo Banners</a> extension.',
                    "https://amasty.com/promo-banners.html");
        }
        $html .= '<p class="note" id="note_preview"><span>' . $message . '</span></p>';
        $html .= '</div>';
        $html .= $this->getAfterElementHtml();

        return $html;
    }
}