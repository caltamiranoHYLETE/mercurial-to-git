<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Adminhtml_Ambannerslite_CategoryController extends Mage_Adminhtml_Controller_Action
{
    /*
     * Action for getting Category Tree (JS tree view) for tab `Access Category Restriction`
     * which is build with AJAX requests calling this action
     */
    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('ambannerslite/adminhtml_promo_quote_edit_tab_products_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ambannerslite/category');
    }
}
