<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * check old rules for banners
     * @return bool
     */
    public function checkRulesForBanners()
    {
        if (Mage::helper('ambase')->isModuleActive('Amasty_Promo')) {
            try {
                $bannerInRules = Mage::getModel('ambannerslite/banners')->getCollectionWithBannersFromPromo();

                if ($bannerInRules->getSize() == 0) {
                    return false;
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function checkRulesForBannersRules()
    {
        if (Mage::helper('ambase')->isModuleActive('Amasty_Rules')
            || Mage::helper('ambase')->isModuleActive('Amasty_RulesPro')
        ) {
            try {
                $collectionRules = Mage::getModel('ambannerslite/banners')->getCollectionWithBannersFromRules();

                $size = $collectionRules->getSize();
                if ($size > 0) {
                    return true;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function isEnable()
    {
        if ($this->checkRulesForBanners()
            && !Mage::helper('ambase')->isModuleActive('Amasty_Rules')
        ) {
            return false;
        }
        if ($this->checkRulesForBannersRules()
            && !Mage::helper('ambase')->isModuleActive('Amasty_Promo')
        ) {
            return false;
        }
        if (Mage::helper('ambase')->isModuleActive('Amasty_Rules')
            && Mage::helper('ambase')->isModuleActive('Amasty_Promo')
            && $this->checkRulesForBanners()
            || $this->checkRulesForBannersRules()
        ) {
            return false;
        }

        return true;
    }
}
