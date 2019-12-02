<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('amrules/banners')}`(
    `entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_id` INT(10) UNSIGNED NOT NULL,
    `top_banner_description` TEXT,
    `top_banner_img` VARCHAR(255) DEFAULT NULL,
    `top_banner_alt` VARCHAR(255) DEFAULT NULL,
    `top_banner_hover_text` VARCHAR(255) DEFAULT NULL,
    `top_banner_link` VARCHAR(255) DEFAULT NULL,
    `after_name_banner_description` TEXT,
    `after_name_banner_img` VARCHAR(255) DEFAULT NULL,
    `after_name_banner_alt` VARCHAR(255) DEFAULT NULL,
    `after_name_banner_hover_text` VARCHAR(255) DEFAULT NULL,
    `after_name_banner_link` VARCHAR(255) DEFAULT NULL,
    `label_img` VARCHAR(255) DEFAULT NULL,
    `label_alt` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`entity_id`, `rule_id`),
    UNIQUE KEY (`rule_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");
$this->endSetup();