<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;

$installer->getConnection()
    ->addColumn($installer->getTable('salesrule/rule'), 'allow_multiple_coupons', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'comment'   => 'Allow several coupons from the same rule',
        'default'   => 0
    ));

