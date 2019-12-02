<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RuleTimeConditions
 */


class Amasty_RuleTimeConditions_Model_Resource_Rule_Collection extends Amasty_RuleTimeConditions_Model_Resource_Rule_Pure
{
    /**
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $couponCode
     * @param null $now - not used
     * @return $this
     */
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode = '', $now = null)
    {
        if (!$this->getFlag('validation_filter')) {
            parent::setValidationFilter($websiteId, $customerGroupId, $couponCode, $now);

            $timestamp = Mage::getModel('core/date')->timestamp();
            $currentTime = date('H:i', $timestamp);
            $currentDay = date('w', $timestamp);

            $this->getSelect()->where("
            (`amrule_use_time` = 0
                OR ('$currentTime' > `amrule_from_time` and ' $currentTime ' < `amrule_to_time`))
            AND (`amrule_use_weekdays` = 0
                OR (`amrule_days_of_week` LIKE '%$currentDay%'))");
        }

        return $this;
    }
}
