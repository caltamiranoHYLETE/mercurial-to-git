<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Block_Adminhtml_Promo_Quote_Edit_Tab_Products_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const VISIBLE = 1;
    /**
     * Amasty_Promo_Block_Adminhtml_Quote_Edit_Tab_Products_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(false);
        $this->setId('ambannersGridPr');
        $this->setUseAjax(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('visibility', array('neq' => self::VISIBLE));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_category',
            array(
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'in_category',
                'values'           => $this->getSavedProducts(),
                'align'            => 'center',
                'index'            => 'entity_id',
                'filter'           => false,
            )
        );

        $this->addColumn(
            'entity_id',
            array(
                'header'   => Mage::helper('catalog')->__('ID'),
                'sortable' => true,
                'width'    => '60',
                'index'    => 'entity_id',
            )
        );

        $this->addColumn(
            'nameProduct',
            array(
                'header'         => Mage::helper('catalog')->__('Name'),
                'index'          => 'name',
                'frame_callback' => array($this, 'addProductLink'),
            )
        );

        $this->addColumn(
            'sku',
            array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width'  => '80',
                'index'  => 'sku',
            )
        );

        $this->addColumn(
            'price',
            array(
                'header'        => Mage::helper('catalog')->__('Price'),
                'type'          => 'currency',
                'width'         => '1',
                'currency_code' => (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                'index'         => 'price',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $url = $this->getUrl('adminhtml/ambannerslite_products/products', array('_current' => true));

        return $url;
    }


    /**
     * @return mixed
     */
    public function getSavedProducts()
    {
        $products = Mage::getModel('ambannerslite/banners')->getProducts($this->_getRuleId());
        if (isset($products[0])) {
            $products = explode(',', $products[0]);
        }

        return $products;
    }

    /**
     * @return mixed
     */
    protected function _getRuleId()
    {
        return (int) $this->getRequest()->getParam('id', 0);
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public function getRowUrl($item)
    {
        return false;
    }

    /**
     * @param $column
     *
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_category') {
            $productIds = $this->getSavedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @param $value
     * @param $row
     *
     * @return string
     */
    public function addProductLink($value, $row)
    {
        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getEntityId()));

        $value = $this->escapeHtml($value);

        return '<a href="' . $url . '">' . $value . '</a>';
    }
}

