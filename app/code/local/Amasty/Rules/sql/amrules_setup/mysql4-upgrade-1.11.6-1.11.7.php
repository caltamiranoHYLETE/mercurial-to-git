<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
$this->startSetup();
$table = $this->getTable('salesrule/rule');

$this->run("
update `{$table}` set `simple_action`='buy_x_get_n_percent',`buy_x_get_n` = 1 where `simple_action`='buy_x_get_y_percent'
");

$this->run("
update `{$table}` set `simple_action`='buy_x_get_n_fixed',`buy_x_get_n` = 1 where `simple_action`='buy_x_get_y_fixed'
");

$this->run("
update `{$table}` set `simple_action`='buy_x_get_n_fixdisc',`buy_x_get_n` = 1  where `simple_action`='buy_x_get_y_fixdisc'
");

$this->run("
update `{$table}` set `simple_action`='each_m_aft_n_perc',`buy_x_get_n` = 'discount_step', `each_m` = 1  where `simple_action`='after_n_disc'
");

$this->run("
update `{$table}` set `simple_action`='each_m_aft_n_disc',`buy_x_get_n` = 'discount_step', `each_m` = 1 where `simple_action`='after_n_fixdisc'
");

$this->run("
update `{$table}` set `simple_action`='each_m_aft_n_fix',`buy_x_get_n` = 'discount_step', `each_m` = 1 where `simple_action`='after_n_fixed'
");

$this->endSetup();