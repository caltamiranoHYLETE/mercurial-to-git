<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_Rule_Eachmaftnperc
    extends Amasty_Rules_Model_Rule_Abstract
{
    function calculateDiscount($rule, $address, $quote)
    {
        $r = array();
        $prices = $this->_getSortedCartPices($rule, $address);
        $qty = $this->_getQty($rule, count($prices));
        if (!$this->hasDiscountItems($prices, $qty)) {
            return $r;
        }

        $qty = max(0, $rule->getDiscountQty()); // qty should be positive
        if ($qty) {
            $qty = min($qty, count($prices));
        } else {
            $qty = count($prices);
        }

        $step = (int)$rule->getDiscountStep();
        $offset = max(0, $step);
        $offset = min($offset, count($prices));

        $step = (int)$rule->getEachM();

        $length = max($qty * $step, count($prices));

        $prices = array_slice($prices, $offset, $length);

        $percentage = floatVal($rule->getDiscountAmount());
        if (!$percentage) {
            $percentage = 100;
        }
        $percentage = ($percentage / 100.0);
        $currQty = 0;
        $lastId = -1;
        foreach ($prices as $i => $price) {

            if ( $this->_skipBySteps($rule, $step, $i-1, $currQty, $qty) ) {
                continue;
            }

            $currQty++;
            $discount = $price['price'] * $percentage;
            $baseDiscount = $price['base_price'] * $percentage;
            if ($price['id'] != $lastId) {
                $lastId = intVal($price['id']);
                $r[$lastId] = array();
                $r[$lastId]['discount'] = $discount;
                $r[$lastId]['base_discount'] = $baseDiscount;
                $r[$lastId]['percent'] = $percentage * 100;
            } else {
                $r[$lastId]['discount'] += $discount;
                $r[$lastId]['base_discount'] += $baseDiscount;
                $r[$lastId]['percent'] = $percentage * 100;
            }
        }

        return $r;
    }
}
