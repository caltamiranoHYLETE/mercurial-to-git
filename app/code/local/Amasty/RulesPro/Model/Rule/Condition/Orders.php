<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */
class Amasty_RulesPro_Model_Rule_Condition_Orders extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('amrules');
        $attributes = array(
            'order_num'    => $hlp->__('Number of Completed Orders'),
            'sales_amount' => $hlp->__('Total Sales Amount'),
            //'prods_qty'    => $hlp->__('Number of Purchased Products'),
        );
        
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getValueSelectOptions()
    {
        $options = array();
        
        $key = 'value_select_options';
        if (!$this->hasData($key)) {
            $this->setData($key, $options);
        }
        return $this->getData($key);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $quote = $object;
        if (!$quote instanceof Mage_Sales_Model_Quote) {
            $quote = $object->getQuote();
        }
        
        $num = 0;
        if ($quote->getCustomerId()){
            
            $resource  = Mage::getSingleton('core/resource');
            $db        = $resource->getConnection('core_read');
            
            $select = $db->select()
                ->from(array('o'=>$resource->getTableName('sales/order')), array())
                ->where('o.customer_id = ?', $quote->getCustomerId())
                ->where('o.status = ?', 'complete')
            ;
            
            if ('order_num' == $this->getAttribute()) {
                $select->from(null, array(new Zend_Db_Expr('COUNT(*)')));
            }
            elseif ('sales_amount' == $this->getAttribute()){
                $select->from(null, array(new Zend_Db_Expr('SUM(o.base_grand_total)')));    
            }

//            elseif ('prods_qty' == $this->getAttribute()){
//                
//                $select->joinInner(array('i'=>$resource->getTableName('sales/order_item')), 'o.entity_id=i.order_id', array(new Zend_Db_Expr('SUM(i.qty_ordered)')))
//                (null, array(new Zend_Db_Expr('SUM(o.base_grand_total)')))
//            }
            
            $num = $db->fetchOne($select);
        }
        
        return $this->validateAttribute($num);
    }
}
