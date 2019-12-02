<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBannersLite
 */


class Amasty_PromoBannersLite_Model_Source_Attributes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();

        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId())
            ->setFrontendInputTypeFilter(array('text', 'textarea'));

        foreach ($collection as $attribute) {
            $label = $attribute->getFrontendLabel();
            if ($label) { // skip system and `exclude` attributes
                $options[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $label
                );
            }
        }

        return $options;
    }

}
