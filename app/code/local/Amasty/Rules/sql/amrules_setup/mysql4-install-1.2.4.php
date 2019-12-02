<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();


$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('salesrule/rule');
$cols = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('promo_sku', $cols)){
    $this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `promo_sku` TEXT");
}
$this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `promo_cats` TEXT");

$sampleData = Mage::getSingleton('amrules/SampleData', array('install'=>1,'tableName'=>'salesrule', 'extensionName'=>'Amasty_Rules'));

$this->endSetup();