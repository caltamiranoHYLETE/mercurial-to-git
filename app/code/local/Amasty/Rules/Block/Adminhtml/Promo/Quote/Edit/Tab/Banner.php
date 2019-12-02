<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Block_Adminhtml_Promo_Quote_Edit_Tab_Banner
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function canShowTab()
    {
        return !Mage::helper('ambannerslite')->isEnable();
    }

    public function getTabLabel()
    {
        return $this->__('Product Page Banners');
    }

    public function getTabTitle()
    {
        return $this->__('Product Page Banners');
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $return;
    }

    protected function _prepareForm()
    {
        $parent =  parent::_prepareForm();
        $model = Mage::getModel('amrules/banners');
        $form = new Varien_Data_Form();

        $fldSet = $form->addFieldset('notice-msg', array('class' => 'notice-msg',));

        $fldSet->addType('message', 'Amasty_Rules_Block_Adminhtml_Varien_Data_Form_Element_Message');

        $fldSet->addField('notice_message', 'message', array(
            'label' => '',
            'name' => 'notice_message')
        );

        $fldSet = $form->addFieldset('top_banner', array('legend' => $this->__('Top Banner')));

        $fldSet->addField('top_banner_img', 'image', array(
            'label'     => $this->__('Image'),
            'name'      => 'top_banner_img',
        ));

        $fldSet->addField('top_banner_alt', 'text', array(
            'label'     => $this->__('Alt'),
            'name'      => 'top_banner_alt',
        ));

        $fldSet->addField('top_banner_hover_text', 'text', array(
            'label'     => $this->__('On Hover Text'),
            'name'      => 'top_banner_hover_text',
        ));

        $fldSet->addField('top_banner_link', 'text', array(
            'label'     => $this->__('Link'),
            'name'      => 'top_banner_link',
        ));
        
        $fldSet->addField('top_banner_description', 'editor', array (
                'name' => 'top_banner_description',
                'label' => $this->__('Description'),
                'title' => $this->__('Description'),
                'style' => 'height:16em;',
                'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'required' => false
        ));

        $fldSet = $form->addFieldset('after_name_banner', array(
            'legend' => $this->__('After Product Description Banner'))
        );

        $fldSet->addField('after_name_banner_img', 'image', array(
            'label'     => $this->__('Image'),
            'name'      => 'after_name_banner_img',
        ));

        $fldSet->addField('after_name_banner_alt', 'text', array(
            'label'     => $this->__('Alt'),
            'name'      => 'after_name_banner_alt',
        ));

        $fldSet->addField('after_name_banner_hover_text', 'text', array(
            'label'     => $this->__('On Hover Text'),
            'name'      => 'after_name_banner_hover_text',
        ));

        $fldSet->addField('after_name_banner_link', 'text', array(
            'label'     => $this->__('Link'),
            'name'      => 'after_name_banner_link',
        ));
        
        $fldSet->addField('after_name_banner_description', 'editor', array (
                'name' => 'after_name_banner_description',
                'label' => $this->__('Description'),
                'title' => $this->__('Description'),
                'style' => 'height:16em;',
                'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'required' => false
        ));

        $fldSet = $form->addFieldset('label', array('legend' => $this->__('Label')));
        
        $fldSet->addField('label_img', 'image', array(
            'label'     => $this->__('Image'),
            'name'      => 'label_img',
        ));
        
        $fldSet->addField('label_alt', 'text', array(
            'label'     => $this->__('Alt'),
            'name'      => 'label_alt',
        ));
        $this->setForm($form);

        $rule = Mage::registry('current_promo_quote_rule');
        if ($rule) {
            $ruleId = $rule->getRuleId();
            $model->loadByRuleId($ruleId);
        }

        $topImg = $model->getTopBannerImg();
        $afterNameImg = $model->getAfterNameBannerImg();
        $labelImg = $model->getLabelImg();

        $linkTop = Mage::helper("amrules/image")->getLink($topImg);
        $linkAfterName = Mage::helper("amrules/image")->getLink($afterNameImg);
        $linkLabel = Mage::helper("amrules/image")->getLink($labelImg);

        $model->setTopBannerImg($linkTop);
        $model->setAfterNameBannerImg($linkAfterName);
        $model->setLabelImg($linkLabel);


        $form->setValues($model->getData());

        return $parent;
    }
}
