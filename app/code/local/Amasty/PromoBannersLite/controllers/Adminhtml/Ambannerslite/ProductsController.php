<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Adminhtml_Ambannerslite_ProductsController extends Mage_Adminhtml_Controller_Action
{
    public function productsAction()
    {
        $grid = $this->getLayout()
            ->createBlock('ambannerslite/adminhtml_promo_quote_edit_tab_products_grid')
            ->setSelectedProducts($this->getRequest()->getPost('selected_products', null));

        // get serializer block html if needed
        $serializerHtml = '';
        if ($this->_firstTimeDisplayedBlock()) {
            $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
            $serializer->initSerializerBlock($grid, 'getSavedProducts', 'selected_products', 'selected_products');
            $serializerHtml = $serializer->toHtml();
        }

        $this->getResponse()->setBody(
            $grid->toHtml() . $serializerHtml
        );
    }

    protected function _firstTimeDisplayedBlock()
    {
        $result = true;

        $params = $this->getRequest()->getParams();
        $keys   = array('sort', 'filter', 'limit', 'page');

        foreach ($keys as $k) {
            if (array_key_exists($k, $params)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ambannerslite/products');
    }
}
