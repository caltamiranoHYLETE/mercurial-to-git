<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */
class Amasty_RulesPro_Model_Mysql4_Calculator extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amrules/calculator', 'entity_id');
    }
    
    public function getTotals($customerId, $conditions, $conditionType)
    {
        $db = $this->_getReadAdapter();
        
        $select = $db->select()
            ->from(array('o' => $this->_resources->getTableName('sales/order')), array())
            ->where('o.customer_id = ?', $customerId)
        ;
        
        $map = array(
            'date'   =>'o.created_at',
            'status' =>'o.status',
        );
        
        foreach ($conditions as $element){
            $value = current($element);
            $field = $map[key($element)];
            $w = $field . ' ' . $value;
            
            if ($conditionType == 'all'){
                $select->where($w);
            } else {
                $select->orWhere($w);
            }
        }         
        
        $select->from(null, array('count' => new Zend_Db_Expr('COUNT(*)'), 'amount' => new Zend_Db_Expr('SUM(o.base_grand_total)')));
        $row = $db->fetchRow($select);
        
        return array('average_order_value' => $row['count'] ? $row['amount'] / $row['count'] : 0,
                     'total_orders_amount' => $row['amount'],
                     'of_placed_orders'    => $row['count'],
                     );
    }
}