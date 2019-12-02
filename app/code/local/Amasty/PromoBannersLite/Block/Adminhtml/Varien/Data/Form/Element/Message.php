<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Block_Adminhtml_Varien_Data_Form_Element_Message extends Varien_Data_Form_Element_Note
{
    public function getElementHtml()
    {
        $html = '<div class="message" id="' . $this->getHtmlId() . '">';
        if (Mage::helper('ambase')->isModuleActive('Amasty_Banners')) {
            $urlToBanners = Mage::helper("adminhtml")->getUrl('adminhtml/ambanners_rule/index');
            $message = Mage::helper('ambannerslite')
                ->__('To work with advanced banners go to <a href="%s" target="_blank">Promo Banners</a>.',
                $urlToBanners);
        } else {
            $message = Mage::helper('ambannerslite')
                ->__('To enrich banners functionality with advanced options please install 
                    <a href="%s" target="_blank">Promo Banners</a> extension.',
                    "https://amasty.com/promo-banners.html");
        }
        $html .= '<p class="note" id="note_preview"><span>' . $message . '</span></p>';
        $html .= '</div>';
        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
