<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Model_Observer
{
    protected $_images = array('ambannerslite_after_name_banner_img', 'ambannerslite_label_img',
        'ambannerslite_top_banner_img');

    public function salesRulePrepareSave($observer)
    {
        foreach ($this->_images as $image) {
            $this->_savePromoRuleImage($observer->getRequest(), $image);
        }
    }

    /**
     * @param $request
     * @param $file
     */
    protected function _savePromoRuleImage($request, $file)
    {
        if ($data = $request->getPost()) {
            if (isset($data[$file]) && isset($data[$file]['delete'])) {
                $data[$file] = null;
            } else if (isset($_FILES[$file]['name']) && $_FILES[$file]['name'] != '') {
                $fileName    = Mage::helper('ambannerslite/image')->upload($file);
                $data[$file] = $fileName;
            } else if (isset($data[$file]['value'])) {
                $data[$file] = basename($data[$file]['value']);
            }

            $request->setPost($data);
        }
    }

    public function saveAfter($observer)
    {
        if (Mage::helper('ambannerslite')->isEnable()) {
            $this->_saveBannersInfo($observer->getRule());
        }
    }

    /**
     * @param $rule
     */
    protected function _saveBannersInfo($rule)
    {
        $data         = $rule->getData();
        $bannersModel = Mage::getModel('ambannerslite/banners');
        $bannersModel = $bannersModel->loadByRuleId($data['rule_id']);
        if (isset($data['selected_products']) && $data['selected_products'] != '') {
            $data['selected_products'] = str_replace('&', ',', $data['selected_products']);
            $bannersModel->setAmbannersliteBannerProducts($data['selected_products']);
        }
        if (isset($data['category_ids']) &&  $data['category_ids'] != "") {
            $bannersModel->setAmbannersliteBannerCategories($data['category_ids']);
        }
        unset($data['banner_id']);

        $bannersModel->addData($data);
        try {
            $bannersModel->save();
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * add enctype = 'multipart/form-data'
     *
     * @param $observer
     */
    public function addEnctypeToForm($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Promo_Quote_Edit_Form) {
            $form = $observer->getBlock()->getForm();
            $form->setData('enctype', 'multipart/form-data');
            $form->setUseContainer(true);
        }
    }

    public function changeSystemConfig(Varien_Event_Observer $observer)
    {
        if (Mage::helper('ambannerslite')->isEnable()) {
            $config = $observer->getConfig();
            $nodeRules = $config->getNode('sections/amrules/groups/banners');
            $nodePromoBanners = $config->getNode('sections/ampromo/groups/banners');
            $nodePromoGiftImages = $config->getNode('sections/ampromo/groups/gift_images');
            if ($nodePromoBanners && $nodePromoGiftImages) {
                $nodePromoBanners->show_in_default = 0;
                $nodePromoBanners->show_in_website = 0;
                $nodePromoBanners->show_in_store = 0;
                $nodePromoGiftImages->show_in_default = 0;
                $nodePromoGiftImages->show_in_website = 0;
                $nodePromoGiftImages->show_in_store = 0;
            }
            if ($nodeRules) {
                $nodeRules->show_in_default = 0;
                $nodeRules->show_in_website = 0;
                $nodeRules->show_in_store = 0;
            }
        }

        return $this;
    }
}
