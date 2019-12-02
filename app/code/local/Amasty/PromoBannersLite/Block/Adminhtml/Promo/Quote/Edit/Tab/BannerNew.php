<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Block_Adminhtml_Promo_Quote_Edit_Tab_BannerNew
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected $_links = array('TopBannerImg', 'AfterNameBannerImg', 'LabelImg');

    public function canShowTab()
    {
        return Mage::helper('ambannerslite')->isEnable();
    }

    public function getTabLabel()
    {
        return $this->__('Product Page Banner');
    }

    public function getTabTitle()
    {
        return $this->__('Product Page Banner');
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if (is_null($headBlock)) {
            $headBlock->addJs('extjs/ext-tree-checkbox.js');
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $return;
    }

    protected function _prepareForm()
    {
        $parent = parent::_prepareForm();
        $model  = Mage::getModel('ambannerslite/banners');

        $form = new Varien_Data_Form();
        $layout = $this->getLayout();
        $grid       = $layout->createBlock('ambannerslite/adminhtml_promo_quote_edit_tab_products_grid');
        $category   = $layout->createBlock('ambannerslite/adminhtml_promo_quote_edit_tab_products_category');
        $serializer = $layout->createBlock('adminhtml/widget_grid_serializer');
        $serializer->initSerializerBlock(
            $grid,
            'getSavedProducts',
            'selected_products',
            'selected_products'
        );

        $this->_initFieldSets($form, $model, $grid, $serializer, $category);

        $this->setForm($form);

        $rule = Mage::registry('current_promo_quote_rule');
        if ($rule) {
            $ruleId = $rule->getRuleId();
            $model->loadByRuleId($ruleId);
        }

        foreach ($this->_links as $link) {
            $this->_addLink($model, $link);
        }

        $values = $model->getData();
        $form->setValues($values);
        $this->_injectDependencies($layout, $model);
        return $parent;
    }

    protected function _addLink($model, $linkName)
    {
        $getLink = 'getAmbannerslite'.$linkName;
        $setLink = 'setAmbannerslite'.$linkName;
        $img = $model->$getLink();
        $link = Mage::helper("ambannerslite/image")->getLink($img);
        $model->$setLink($link);
    }

    protected function _initFieldSets($form, $model, $grid, $serializer, $category)
    {
        $this->_addMessage($form);
        $this->_addDisplayOptions($form, $model, $grid, $serializer, $category);
        $this->_addTopBanner($form, $model);
        $this->_addAfterBanner($form, $model);
        $this->_addProductLabel($form, $model);
    }

    protected function _addMessage($form)
    {
        $fldSet = $form->addFieldset('notice-msg', array('class' => 'notice-msg',));

        $fldSet->addType('message', 'Amasty_PromoBannersLite_Block_Adminhtml_Varien_Data_Form_Element_Message');

        $fldSet->addField('
            notice_message',
            'message',
            array(
                'label' => '',
                'name' => 'notice_message'
            )
        );
    }

    protected function _addDisplayOptions($form, $model, $grid, $serializer, $category)
    {
        $fldSet = $form->addFieldset(
            'ambannerslite_banner_general',
            array(
                'legend' => $this->__('Display Options'),
            )
        );

        $type = $fldSet->addField(
            'ambannerslite_banner_type',
            'select',
            array(
                'name'   => 'ambannerslite_banner_type',
                'label'  => $this->__('Show Banner for'),
                'title'  => $this->__('Show Banner for'),
                'values' => array(
                    $model::PRODUCT  => $this->__('All products'),
                    $model::SKU      => $this->__('Products sku'),
                    $model::CATEGORY => $this->__('Categories'),
                ),
            )
        );

        $this->setAmbannersliteBannerType($type);

        $fldSet->addField(
            'ambannerslite_banner_products',
            'hidden',
            array(
                'after_element_html' => $grid->toHtml() . $serializer->toHtml(),
            )
        );

        $fldSet->addField(
            'ambannerslite_banner_categories',
            'hidden',
            array(
                'after_element_html' => $category->toHtml(),
            )
        );
    }

    protected function _addTopBanner($form, $model)
    {
        $fldSet = $form->addFieldset(
            'ambannerslite_top_banner',
            array(
                'legend' => $this->__('Top Banner'),
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_enable',
            'select',
            array(
                'name'    => 'ambannerslite_top_banner_enable',
                'label'   => $this->__('Enabled'),
                'title'   => $this->__('Enabled'),
                'options' => array(
                    $model::DISABLE => $this->__('No'),
                    $model::ENABLE  => $this->__('Yes'),
                ),
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_img',
            'image',
            array(
                'label' => $this->__('Image'),
                'name'  => 'ambannerslite_top_banner_img',
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_alt',
            'text',
            array(
                'label' => $this->__('Alt'),
                'name'  => 'ambannerslite_top_banner_alt',
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_hover_text',
            'text',
            array(
                'label' => $this->__('On Hover Text'),
                'name'  => 'ambannerslite_top_banner_hover_text',
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_link',
            'text',
            array(
                'label' => $this->__('Link'),
                'name'  => 'ambannerslite_top_banner_link',
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_gift_images_enable',
            'select',
            array(
                'name'    => 'ambannerslite_top_banner_gift_images_enable',
                'label'   => $this->__('Show Gift images'),
                'title'   => $this->__('Show Gift images'),
                'options' => array(
                    0 => $this->__('No'),
                    1 => $this->__('Yes'),
                ),
                'note'    => 'works with sku conditions',
            )
        );

        $fldSet->addField(
            'ambannerslite_top_banner_description',
            'editor',
            array(
                'name'     => 'ambannerslite_top_banner_description',
                'label'    => $this->__('Description'),
                'title'    => $this->__('Description'),
                'style'    => 'height:16em;',
                'config'   => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'required' => false,
            )
        );
    }

    protected function _addAfterBanner($form, $model)
    {
        $fldSet = $form->addFieldset(
            'ambannerslite_after_name_banner',
            array(
                'legend' => $this->__('After Product Description Banner'),
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_enable',
            'select',
            array(
                'name'    => 'ambannerslite_after_name_banner_enable',
                'label'   => $this->__('Enabled'),
                'title'   => $this->__('Enabled'),
                'options' => array(
                    $model::DISABLE => $this->__('No'),
                    $model::ENABLE  => $this->__('Yes'),
                ),
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_img',
            'image',
            array(
                'label' => $this->__('Image'),
                'name'  => 'ambannerslite_after_name_banner_img',
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_alt',
            'text',
            array(
                'label' => $this->__('Alt'),
                'name'  => 'ambannerslite_after_name_banner_alt',
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_hover_text',
            'text',
            array(
                'label' => $this->__('On Hover Text'),
                'name'  => 'ambannerslite_after_name_banner_hover_text',
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_link',
            'text',
            array(
                'label' => $this->__('Link'),
                'name'  => 'ambannerslite_after_name_banner_link',
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_gift_images_enable',
            'select',
            array(
                'name'    => 'ambannerslite_after_name_banner_gift_images_enable',
                'label'   => $this->__('Show Gift images'),
                'title'   => $this->__('Show Gift images'),
                'options' => array(
                    0 => $this->__('No'),
                    1 => $this->__('Yes'),
                ),
                'note'    => 'works with sku conditions',
            )
        );

        $fldSet->addField(
            'ambannerslite_after_name_banner_description',
            'editor',
            array(
                'name'     => 'ambannerslite_after_name_banner_description',
                'label'    => $this->__('Description'),
                'title'    => $this->__('Description'),
                'style'    => 'height:16em;',
                'config'   => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'required' => false,
            )
        );
    }

    protected function _addProductLabel($form, $model)
    {
        $fldSet = $form->addFieldset(
            'ambannerslite_label',
            array(
                'legend' => $this->__('Product label (ribbon)'),
            )
        );

        $fldSet->addField(
            'ambannerslite_label_enable',
            'select',
            array(
                'name'    => 'ambannerslite_label_enable',
                'label'   => $this->__('Enabled'),
                'title'   => $this->__('Enabled'),
                'options' => array(
                    $model::DISABLE => $this->__('No'),
                    $model::ENABLE  => $this->__('Yes'),
                ),
            )
        );

        $fldSet->addField(
            'ambannerslite_label_img',
            'image',
            array(
                'label' => $this->__('Image'),
                'name'  => 'ambannerslite_label_img',
            )
        );

        $fldSet->addField(
            'ambannerslite_label_labels_extension',
            'note',
            array(
                'after_element_html' => $this->__('Get attractive and various labels with <a href="%s" target="_blank">"Product Labels"</a> extension.',
                    "https://amasty.com/product-labels.html"),
            )
        );

        $fldSet->addField(
            'ambannerslite_label_alt',
            'text',
            array(
                'label' => $this->__('Alt'),
                'name'  => 'ambannerslite_label_alt',
            )
        );
    }

    protected function _injectDependencies($layout, $model)
    {
        $type = $this->getAmbannersliteBannerType();
        $this->setChild(
            'form_after',
            $layout
                ->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($type->getHtmlId(), $type->getName())
                ->addFieldMap('ambannerslite_banner_products', 'ambannerslite_banner_products')
                ->addFieldMap('ambannerslite_banner_categories', 'ambannerslite_banner_categories')
                ->addFieldDependence(
                    'ambannerslite_banner_products',
                    $type->getName(),
                    $model::SKU
                )
                ->addFieldDependence(
                    'ambannerslite_banner_categories',
                    $type->getName(),
                    $model::CATEGORY
                )
        );
    }
}
