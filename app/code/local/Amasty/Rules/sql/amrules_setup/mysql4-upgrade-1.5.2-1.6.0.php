<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


$this->startSetup();


$db = $this->getConnection();


$fieldsSql = 'SELECT website_id FROM  ' . $this->getTable('core/website');
$cols = $this->getConnection()->fetchAll($fieldsSql);
foreach ($cols as $col){
    $websites[] = $col['website_id'];
}

$fieldsSql = 'SELECT customer_group_id FROM  ' . $this->getTable('customer/customer_group');
$cols = $this->getConnection()->fetchAll($fieldsSql);
foreach ($cols as $col){
    $cusgroups[] = $col['customer_group_id'];
}


$rule = Mage::getModel('salesrule/rule');
$rule->setName('AmastyXY')
    ->setDescription('Please do NOT delete!')
    ->setCouponCode('')
    ->setCustomerGroupIds($cusgroups) //an array of customer grou pids
    ->setIsActive(1)
    //serialized conditions.  the following examples are empty
    //->setConditionsSerialized('a:6:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}')
    //->setActionsSerialized('a:6:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}')
    ->setStopRulesProcessing(0)
    ->setSortOrder(9999)
    ->setSimpleAction('by_percent')
    ->setDiscountAmount(0)
    ->setDiscountQty(null)
    ->setIsRss(0)
    ->setWebsiteIds($websites);
$rule->save();

$this->endSetup();