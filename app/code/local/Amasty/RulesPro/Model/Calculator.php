<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */
class Amasty_RulesPro_Model_Calculator extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amrulespro/calculator');
    }

    public function getThisMonthTotal($customerId)
    {
        $from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('t'), date('Y')));

        $conditions[] = array('date' => ' >= "' . $from . '"');
        $conditions[] = array('date' => ' <= "' . $to . '"');
        $conditions[] = array('status' => ' = "complete"');
        return $this->_getTotals($customerId, $conditions);
    }

    public function getLastMonthTotal($customerId)
    {
        $y = date('Y');
        $m = date('m');
        if (0 == $m - 1) {
            $y = $y - 1;
            $m = 12;
        } else {
            $m = $m - 1;
        }
        $last = mktime(0, 0, 0, $m, 1, $y);

        $from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $last), 1, date('Y', $last)));
        $to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $last), date('t', $last), date('Y', $last)));

        $conditions[] = array('date' => ' >= "' . $from . '"');
        $conditions[] = array('date' => ' <= "' . $to . '"');
        $conditions[] = array('status' => ' = "complete"');

        return $this->_getTotals($customerId, $conditions);

    }

    public function getAllPeriodTotal($customerId)
    {
        $conditions[] = array('status' => ' = "complete"');
        return $this->_getTotals($customerId, $conditions);
    }

    public function getLastYear($customerId)
    {
        $from = strtotime ( '-1 year' , strtotime ( date('Y-m-d H:i:s') ) ) ;
        $from = date ( 'Y-m-d H:i:s' , $from );

        $conditions[] = array('date' => ' >= "' . $from . '"');
        $conditions[] = array('status' => ' = "complete"');

        return $this->_getTotals($customerId, $conditions);
    }

    public function getSingleTotalField($customerId, $fieldName, $conditions, $conditionType)
    {
        $result = $this->_getTotals($customerId, $conditions, $conditionType);
        return $result[$fieldName];
    }

    /**
     * Calculates aggregated order values for given customer
     *
     * @param int $customerId
     * @param array $conditions  e.g. array( 0=> array('date'=>'>2013-12-04'),  1=>array('status'=>'>2013-12-04'))
     * @param string $conditionType "all"  or "any"
     */
    protected function _getTotals($customerId, $conditions=array(), $conditionType='all')
    {
        return $this->getTotals($customerId, $conditions, $conditionType);
    }

    public function getTotals($customerId, $conditions, $conditionType)
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');;

        $select = $db->select()
            ->from(array('o' => Mage::getSingleton('core/resource')->getTableName('sales/order')), array())
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

        $select->from(null, array('count' => new Zend_Db_Expr('COUNT(*)'), 'amount' => new Zend_Db_Expr('SUM(o.base_grand_total) - IFNULL(o.base_total_refunded,0)')));
        $row = $db->fetchRow($select);

        return array('average_order_value' => $row['count'] ? $row['amount'] / $row['count'] : 0,
            'total_orders_amount' => $row['amount'],
            'of_placed_orders'    => $row['count'],
        );
    }
    
}