<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();

$sampleData = Mage::getSingleton('amrules/SampleData', array('tableName'=>'salesrule', 'extensionName'=>'Amasty_Rules'));

$sampleData->import();


$this->endSetup();