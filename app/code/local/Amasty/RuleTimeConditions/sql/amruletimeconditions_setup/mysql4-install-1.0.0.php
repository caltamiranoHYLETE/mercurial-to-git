<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RuleTimeConditions
 */


/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$connection = $this->getConnection();
$table = $this->getTable('salesrule');

$connection->addColumn($table, 'amrule_days_of_week', "VARCHAR(255) NOT NULL");
$connection->addColumn($table, 'amrule_from_time', "TIME");
$connection->addColumn($table, 'amrule_to_time', "TIME");
$connection->addColumn($table, 'amrule_use_time', "BOOLEAN NOT NULL default '0'");
$connection->addColumn($table, 'amrule_use_weekdays', "BOOLEAN NOT NULL default '0'");

$this->endSetup();
