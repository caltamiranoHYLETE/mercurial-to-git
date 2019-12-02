<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Block_Adminhtml_Promo_Quote_Edit_Tab_Products_Category
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{

    protected $_categories;

    public function isReadonly()
    {
        return false;
    }

    /**
     * @return array / string
     */
    protected function getCategoryIds()
    {
        $array = array();
        $id    = (int)$this->getRequest()->getParam('id');
        if (empty($this->_categories)) {
            $data = Mage::getModel('ambannerslite/banners')->getCollection()
                ->addFieldToFilter('rule_id', $id)
                ->setPageSize(1)
                ->getFirstItem()
                ->getData('ambannerslite_banner_categories');

            if ($data) {
                $array = explode(',', $data);
            } else {
                $array[] = "";
            }
            $this->_categories = $array;
        }

        return $this->_categories;
    }

    /**
     * @param array|Varien_Data_Tree_Node $node
     * @param int $level
     *
     * @return string
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = parent::_getNodeJson($node, $level);

        $isParent = $this->_isParentSelectedCategory($node);

        if ($isParent) {
            $item['expanded'] = true;
        }

        if (in_array($node->getId(), $this->getCategoryIds())) {
            $item['checked'] = true;
        }

        return $item;
    }


    /**
     * @param null $expanded
     *
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        $url = $this->getUrl('adminhtml/ambannerslite_category/categoriesJson', array('_current' => true));

        return $url;
    }
}
