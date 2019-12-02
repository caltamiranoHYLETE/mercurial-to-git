<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */

$this->startSetup();

$this->run(
    "CREATE TABLE IF NOT EXISTS `{$this->getTable('ambannerslite/banners')}`(
        `ambanner_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `rule_id` INT(10) UNSIGNED NOT NULL,
        `ambannerslite_banner_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        `ambannerslite_top_banner_enable` TINYINT(1) NOT NULL DEFAULT '0',
        `ambannerslite_label_enable` TINYINT(1) NOT NULL DEFAULT '0',
        `ambannerslite_after_name_banner_enable` TINYINT(1) NOT NULL DEFAULT '0',
        `ambannerslite_banner_categories` TEXT DEFAULT NULL,
        `ambannerslite_banner_products` TEXT DEFAULT NULL,
        `ambannerslite_top_banner_img` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_top_banner_alt` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_top_banner_hover_text` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_top_banner_link` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_top_banner_gift_images_enable` TINYINT(1) NOT NULL DEFAULT '0',
        `ambannerslite_top_banner_description` TEXT DEFAULT NULL,
        `ambannerslite_after_name_banner_img` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_after_name_banner_alt` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_after_name_banner_hover_text` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_after_name_banner_link` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_after_name_banner_gift_images_enable` TINYINT(2) NOT NULL DEFAULT '0',
        `ambannerslite_after_name_banner_description` TEXT DEFAULT NULL,
        `ambannerslite_label_img` VARCHAR(255) DEFAULT NULL,
        `ambannerslite_label_alt` VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (`ambanner_id`, `rule_id`),
        UNIQUE KEY (`rule_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
);

$this->endSetup();