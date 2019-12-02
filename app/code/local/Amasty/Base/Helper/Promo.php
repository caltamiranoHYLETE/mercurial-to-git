<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Helper_Promo extends Mage_Core_Helper_Abstract
{
    public function getNotificationsCollection()
    {
        $collection = null;
        $inbox = Mage::getModel("adminnotification/inbox");

        if ($inbox) {
            $collection = $inbox->getCollection();

            if ($collection) {
                $collection->getSelect()
                        ->where('title like "%amasty%" or description like "%amasty%" or url like "%amasty%"')
                        ->where('is_read != 1')
                        ->where('is_remove != 1');
            }

        }

        return $collection;
    }

    public function isSubscribed()
    {
        return Mage::getStoreConfig('ambase/feed/promo') == 1;
    }
}
