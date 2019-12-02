<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */


class Amasty_Coupons_Model_Admin_Observer extends Mage_Core_Model_Abstract 
{

	public function salesrulePrepareForm( $observer )
	{
	    $form 				= $observer->getForm() ;
		if(is_object($form)){
		    $model = Mage::registry('current_promo_quote_rule');
		    $multipleCouponsValue = $model->getAllowMultipleCoupons(0);
		    $fieldset = $form->getForm()->getElement('base_fieldset');
			$note = '';
			$disabled = false;
			if (Mage::getStoreConfig('amcoupons/codes/allow_same_rule')) {
				$note = 'To enable this setting please go to
				<a href="'. Mage::helper("adminhtml")->getUrl('adminhtml/system_config/edit', array('section' => 'amcoupons')) . '">System -> Configuration -> Multiple Coupons</a>
				and set \'Allow several coupons from the same rule\' option to \'No\'';
				$disabled = true;
				$multipleCouponsValue = 1;
			}
	        $fieldset->addField('allow_multiple_coupons', 'select', array(
	            'name'		=> 'allow_multiple_coupons',
	            'label'		=> Mage::helper('salesrule')->__('Allow several coupons from the same rule'),
	            'title'		=> Mage::helper('salesrule')->__('Allow several coupons from the same rule'),
                'options'   => array(
                    '1' => Mage::helper('salesrule')->__('Yes'),
                    '0' => Mage::helper('salesrule')->__('No'),
                ),
                'note' 		=> Mage::helper('salesrule')->__($note),
				'disabled'  => $disabled,
	            'value'     => $multipleCouponsValue
	        ))
	        ;
    	}
	}
}
