<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_SalesRule_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function validate(Varien_Object $object, $checkX = false)
    {
        // for optimization if we no conditions
        /*
        if (!$this->getConditions()) {
            return true;
        }
*/
        //remember original product
        $origProduct = $object->getProduct();
        $origSku     = $object->getSku();

        $action = $this->getRule()->getSimpleAction();

        $promoResult = $this->validatePromoItems($object, $action, $checkX);
        if ($promoResult !== null) {
            return $promoResult;
        }

        if ($object->getHasChildren() && $object->getProductType() == 'configurable') {
            foreach ($object->getChildren() as $child) {
                // only one itereation.
                $categoryIds = array_merge($child->getProduct()->getCategoryIds(), $origProduct->getCategoryIds());
                $categoryIds = array_unique($categoryIds);
                $object->setProduct($child->getProduct());
                $object->setSku($child->getSku());
                $object->getProduct()->setCategoryIds($categoryIds);
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Amasty_Promo')) {
            $validator = new Amasty_Promo_Model_SalesRule_Rule_Condition_Product_Combine();
        } else {
            $validator = new Mage_Rule_Model_Condition_Combine();
        }
        $validator->setData($this->getData());
        $result = $validator->validate($object);
        $this->setData($validator->getData());

        if ($origProduct) {
            // restore original product
            $object->setProduct($origProduct);
            $object->setSku($origSku);
        }

        return $result;
    }

    /**
     * @param Varien_Object $object
     *
     * @return bool|null   if null - continue validation
     */
    protected function validatePromoItems(Varien_Object $object, $action, $checkX)
    {
        if (strpos($action, "buy_x_get_") !== false && $action !== Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION) {
            $promoCats = Mage::helper('amrules')->getRuleCats($this->getRule());
            $promoSku  = Mage::helper('amrules')->getRuleSkus($this->getRule());
            $itemSku   = $object->getSku();
            $itemCats  = $object->getCategoryIds();

            if (!$itemCats) {
                $itemCats = $object->getProduct()->getCategoryIds();
            }

            $parent = $object->getParentItem();

            if (Mage::helper('amrules')->isConfigurablePromoItem($object, $promoSku)) {
                return true;
            }

            if ($parent) {
                $parentType = $parent->getProductType();
                if ($parentType == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $itemSku  = $object->getParentItem()->getProduct()->getSku();
                    $itemCats = $object->getParentItem()->getProduct()->getCategoryIds();
                }
            }

            if (in_array($itemSku, $promoSku) || ($itemCats !== null && array_intersect($promoCats, $itemCats))) {
                return true;
            }
            if (!$checkX) {
                return false;
            }
        }

        return null;
    }

    public function getGrandparentClass($thing)
    {
        if (is_object($thing)) {
            $thing = get_class($thing);
        }
        $class = new ReflectionClass($thing);

        return $class->getParentClass()->getParentClass()->getName();
    }
}
