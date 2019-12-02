<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Block_Adminhtml_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{
    public function __construct()
    {
        parent::__construct();
        $strWithNewName                  = $this->__('Number Of Gift Items');
        $strWithOldName                  = $this->__('Discount Amount');

        $default            = array(
            Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION,
            Mage_SalesRule_Model_Rule::BY_FIXED_ACTION,
            Mage_SalesRule_Model_Rule::CART_FIXED_ACTION,
            Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION,
        );

        $default = "'" . implode("','", $default) . "'";

        $percent            = Amasty_Rules_Helper_Data::TYPE_XY_PERCENT;
        $fixed              = Amasty_Rules_Helper_Data::TYPE_XY_FIXED;
        $fixdisc            = Amasty_Rules_Helper_Data::TYPE_XY_FIXDISC;

        $buyxgetnfixed      = Amasty_Rules_Helper_Data::TYPE_XN_FIXED;
        $buyxgetnpercent    = Amasty_Rules_Helper_Data::TYPE_XN_PERCENT;
        $buyxgetnfixdisc    = Amasty_Rules_Helper_Data::TYPE_XN_FIXDISC;


        $setof_percent      = Amasty_Rules_Helper_Data::TYPE_SETOF_PERCENT;
        $setof_fixed        = Amasty_Rules_Helper_Data::TYPE_SETOF_FIXED;
        $each_m_perc        = Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_PERC;
        $each_m_disc        = Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_DISC;
        $each_m_fix         = Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_FIX;
        
        $each_n_disc        = Amasty_Rules_Helper_Data::TYPE_EACH_N;
        $each_n_fixdisc     = Amasty_Rules_Helper_Data::TYPE_EACH_N_FIXDISC;
        $each_n_fix         = Amasty_Rules_Helper_Data::TYPE_FIXED;

        $each_group_of_n_disc   = Amasty_Rules_Helper_Data::TYPE_GROUP_N_DISC;
        $each_group_of_n        = Amasty_Rules_Helper_Data::TYPE_GROUP_N;



        // ampromo - is correct name, it is for compatibility with Auto Add Promo Items
        $this->_formScripts[] = "
			function ampromo_note() {
                var selectNote = $('rule_simple_action_note');
                var select = $('rule_simple_action');
                if (!selectNote) {
                    select.insert({
                        after: new Element('p', {class: 'note', id: 'rule_simple_action_note'})
                    });

                    selectNote = $('rule_simple_action_note');
                }

                var noteTemplate = new Template('" . $this->__('Please see <a href="https://amasty.com/knowledge-base/special-promotions-#{value}.html">usage example</a>') . ".');
                selectNote.update( noteTemplate.evaluate({value: select.value.split('_').join('-')}) );
			}
			
			function getAmpromoNote(show) {
			    var selectNote = $('rule_simple_action_note2');
                var select = $('rule_simple_action');
                if (!selectNote) {
                    select.insert({
                        after: new Element('p', {class: 'note', id: 'rule_simple_action_note2'})
                    });

                    selectNote = $('rule_simple_action_note2');
                }
                if (show) {
                    selectNote.update('It gives Y product specified discount, but does not add the product Y automatically. For auto-adding please consider <a href=\"https://amasty.com/magento-free-gift.html\">Free Gift module</a>');
                } else {
                    selectNote.update('');
                }
			}
			
			function amrules_hint() {
			    $('note_price_selector').hide();
                if ($('rule_price_selector').value == ".Amasty_Rules_Model_Observer::BASED_ON_BIGGEST.") {
                    $('note_price_selector').show();
                }
			}
			
			function renameDiscountAmountField(fieldName) {
			    $$(\"label[for='rule_discount_amount']\")[0].update(fieldName+'<span class=\"required\">*</span>');
			}
			
			function renameDiscountStepField(fieldName) {
			    $$(\"label[for='rule_discount_step']\")[0].update(fieldName+'<span class=\"required\">*</span>');
			}
			
			function hideMaxDiscountField() {
			    $('rule_max_discount').up().hide();
			    $$(\"label[for='rule_max_discount']\")[0].hide();
			}
			
			function removeOrSetValidatorClassName(element, className, method) {
			    switch (method) {
			        case 'set':
                        className.each(function(name) {
                            element.addClassName(name);
                        });
			           break;
                    case 'remove':
                        className.each(function(name) {
                            element.removeClassName(name);
                        });
                       break;
			    }
			}
			
			function fieldsetChange(id, method) {
			    switch (method) {
			        case 'hide':
			           $(id).hide();
                       $(id).previous().hide();
			           break;
                    case 'show':
                       $(id).show();
                       $(id).previous().show();
                       break;
			    }

			}
			
			function movingDomBetweenElements(id_base, id_move) {
			    $(id_base).down().down().down().insert($(id_move).up().up());
			}
			
						
			function ampromo_hide() {
			    var elementWithZeroValidation = ['rule_buy_x_get_n', 'rule_each_m', 'rule_discount_step'];
			if ($$(\"label[for='ambannerslite_top_banner_gift_images_enable']\")[0]) {
			    $$(\"label[for='ambannerslite_top_banner_gift_images_enable']\")[0].update('".$this->__('Show Promo Items image')."');
			}
			if ($$(\"label[for='ambannerslite_after_name_banner_gift_images_enable']\")[0]) {
			    $$(\"label[for='ambannerslite_after_name_banner_gift_images_enable']\")[0].update('".$this->__('Show Promo Items image')."');
			}
			    movingDomBetweenElements('rule_action_fieldset', 'rule_promo_sku');
				movingDomBetweenElements('rule_action_fieldset', 'rule_promo_cats');
			    $('rule_max_discount').up().show();
			    $$(\"label[for='rule_max_discount']\")[0].show();
			    $('rule_apply_to_shipping').up().hide();
			    $$(\"label[for='rule_apply_to_shipping']\")[0].hide();
			    $$(\"p[id='note_discount_step']\")[0].hide();
			    $('rule_y_product_fieldset').previous().update('<h4>".$this->__('Define Y product (X and Y are different products)')."</h4>');
			    renameDiscountAmountField('".$this->__('Discount Amount')."');
			    elementWithZeroValidation.forEach(function(element) {
			        removeOrSetValidatorClassName($(element), ['validate-greater-than-zero', 'required-entry'], 'remove');
			    });

			    renameDiscountStepField('".$this->__('Discount Qty Step ')."');
			    $('rule_actions_fieldset').up().select('h4')[0].update('".$this->__('Apply the rule to the following products only (leave blank for all products)')."')
			    $('rule_discount_qty').up().up().show();
			    fieldsetChange('rule_y_product_fieldset', 'hide');
			    
			    switch ($('rule_simple_action').value) {
			        //default magento action
			        case 'buy_x_get_y':
			            renameDiscountAmountField('".$this->__('Number of Products with Discount')."');
			            renameDiscountStepField('".$this->__('Buy N Products')."');
//			            hideMaxDiscountField();
			            break;
			        case '".Amasty_Rules_Helper_Data::TYPE_AMOUNT."':
			            //renameDiscountAmountField('".$this->__('Cashback ')."');
			            $('rule_discount_qty').up().up().show();
			            renameDiscountStepField('".$this->__('Amount to Spend')."');
			            break;
			        case '".Amasty_Rules_Helper_Data::TYPE_CHEAPEST."':
			        case '".Amasty_Rules_Helper_Data::TYPE_EXPENSIVE."':
			        case '".Amasty_Rules_Helper_Data::TYPE_XY_PERCENT."':
			        case '".Amasty_Rules_Helper_Data::TYPE_XN_PERCENT."':
			        case '".Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_PERC."':
			        case '".Amasty_Rules_Helper_Data::TYPE_GROUP_N_DISC."':
			        case '".Amasty_Rules_Helper_Data::TYPE_AFTER_N_DISC."':
			        case '".Amasty_Rules_Helper_Data::TYPE_SETOF_PERCENT."':
			            renameDiscountAmountField('".$this->__('Discount Amount (in %) ')."');
			            break;
			        case '".Amasty_Rules_Helper_Data::TYPE_AMOUNT."':
			        case '".Amasty_Rules_Helper_Data::TYPE_XY_FIXDISC."':
			        case '".Amasty_Rules_Helper_Data::TYPE_XN_FIXDISC."':
			        case '".Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_DISC."':
			        case '".Amasty_Rules_Helper_Data::TYPE_AFTER_N_FIXDISC."':
			            renameDiscountAmountField('".$this->__('Discount Amount ')."');
			            hideMaxDiscountField();
			            break;
			        case '".Amasty_Rules_Helper_Data::TYPE_XY_FIXED."':
			        case '".Amasty_Rules_Helper_Data::TYPE_XN_FIXED."':
			        case '".Amasty_Rules_Helper_Data::TYPE_EACH_M_AFT_N_FIX."':
			        case '".Amasty_Rules_Helper_Data::TYPE_AFTER_N_FIXED."':
			            renameDiscountAmountField('".$this->__('Final Promo Item Price ')."');
			            hideMaxDiscountField();
			            break;
			        case '".Amasty_Rules_Helper_Data::TYPE_GROUP_N."':
			            renameDiscountAmountField('".$this->__('Final Price For Group ')."');
			            hideMaxDiscountField();
			            break;
			        case '".Amasty_Rules_Helper_Data::TYPE_SETOF_FIXED."':
			            renameDiscountAmountField('".$this->__('Final Set Price ')."');
			            hideMaxDiscountField();
			            break;
			            case 'ampromo_items':
                    case 'ampromo_cart':
                    case 'ampromo_product':
                    case 'ampromo_spent':
                    if ($$(\"label[for='ambannerslite_top_banner_gift_images_enable']\")[0]) {
                        $$(\"label[for='ambannerslite_top_banner_gift_images_enable']\")[0].update('".$this->__('Show Gift Images')."');
                    }
                    if ($$(\"label[for='ambannerslite_after_name_banner_gift_images_enable']\")[0]) {
			            $$(\"label[for='ambannerslite_after_name_banner_gift_images_enable']\")[0].update('".$this->__('Show Gift Images')."');
                    }
                        break;
			        default:
			            renameDiscountAmountField('".$this->__('Discount Amount ')."');
			            $('rule_apply_to_shipping').up().show();
			            $$(\"label[for='rule_apply_to_shipping']\")[0].show();
			    }
			    
                $('rule_discount_step').up().up().show();
                $$('div.rule-tree').each(Element.show);

//                var s = $('rule_apply_to_shipping');
                if (s) s.up().up().show();

                $('rule_actions_fieldset').up().show();
                $('rule_promo_sku').up().up().hide();
                $('rule_promo_cats').up().up().hide();
                $('rule_each_m').up().up().hide();
                $('rule_buy_x_get_n').up().up().hide();
                //$('rule_ampromo_type').up().up().hide();

                var s = $('rule_ampromo_type');
                if (s) s.up().up().hide();
                getAmpromoNote(0);

                //$$('label[for=\"rule_discount_step\"]').first().update('".$this->__('Number of X Products')."')

                $('rule_simple_free_shipping').up().up().show();

                $('rule_price_selector').up().up().show();
                $('rule_max_discount').up().up().show();

                if ($('rule_simple_action').value=='by_percent' || $('rule_simple_action').value=='by_fixed'
                || $('rule_simple_action').value=='cart_fixed' || $('rule_simple_action').value=='buy_x_get_y')
                {
                    $('rule_price_selector').up().up().hide();
                }

                if ('ampromo_cart' == $('rule_simple_action').value) {
                    $('rule_simple_free_shipping').up().up().hide();

                    $('rule_actions_fieldset').up().hide();
                    $('rule_discount_qty').up().up().hide();
                    $('rule_discount_step').up().up().hide();

                    if (s) s.up().up().hide();
                    $('rule_promo_sku').up().up().show();
                    $('rule_ampromo_type').up().up().show();
                }
                if ('ampromo_items' == $('rule_simple_action').value){
                    $('rule_simple_free_shipping').up().up().hide();

                    if (s) s.up().up().hide();
                    $('rule_promo_sku').up().up().show();
                    $('rule_ampromo_type').up().up().show();
                }
                if ('ampromo_product' == $('rule_simple_action').value){
                    $('rule_simple_free_shipping').up().up().hide();

                    if (s) s.up().up().hide();
                }
                if ('ampromo_spent' == $('rule_simple_action').value){
                    $('rule_simple_free_shipping').up().up().hide();

                    if (s) s.up().up().hide();

                    $('rule_promo_sku').up().up().show();
                    $('rule_ampromo_type').up().up().show();
                }
                
                $('rule_amskip_rule').up().up().show();
                
                var defaultRules = [$default];
                if(defaultRules.indexOf($('rule_simple_action').value) != -1) {
                    $('rule_amskip_rule').up().up().hide();
                    hideMaxDiscountField();
                }

                if ('$setof_percent' == $('rule_simple_action').value || '$setof_fixed' == $('rule_simple_action').value){
//                    $('rule_apply_to_shipping').up().up().hide();
                    $('rule_discount_step').up().up().hide();
                    //$('.rule-tree').hide();

                    $$('div.rule-tree').each(Element.hide);
                    fieldsetChange('rule_y_product_fieldset', 'show');
                    movingDomBetweenElements('rule_y_product_fieldset', 'rule_promo_sku');
					movingDomBetweenElements('rule_y_product_fieldset', 'rule_promo_cats');
					$('rule_y_product_fieldset').previous().update('<h4>".$this->__('Product Set')."</h4>');

                }
                
                if ('$each_n_disc' == $('rule_simple_action').value || '$each_n_fixdisc' == $('rule_simple_action').value || '$each_n_fix' == $('rule_simple_action').value){
                    renameDiscountStepField('".$this->__('Each N-th ')."');
                }
                
                if ('$each_group_of_n_disc' == $('rule_simple_action').value || '$each_group_of_n' == $('rule_simple_action').value) {
                    renameDiscountStepField('".$this->__('Each Group of N ')."');
                }

                if ('$percent' == $('rule_simple_action').value || '$fixed' == $('rule_simple_action').value || '$fixdisc' == $('rule_simple_action').value){
//                    $('rule_apply_to_shipping').up().up().hide();
                    getAmpromoNote(1);
                }

                if ('$each_m_perc' == $('rule_simple_action').value || '$each_m_disc' == $('rule_simple_action').value || '$each_m_fix' == $('rule_simple_action').value){
                    $('rule_each_m').up().up().show();
                    removeOrSetValidatorClassName($('rule_each_m'), ['validate-greater-than-zero'], 'set');
                    $$('label[for=\"rule_discount_step\"]').first().update('".$this->__('After N')."')
                }

				if ('$percent' == $('rule_simple_action').value || '$fixed' == $('rule_simple_action').value || '$fixdisc' == $('rule_simple_action').value || 
                '$setof_percent' == $('rule_simple_action').value || '$setof_fixed' == $('rule_simple_action').value){

					$('rule_promo_sku').up().up().show();
					$('rule_promo_cats').up().up().show();
				}

                if ('$buyxgetnfixed' == $('rule_simple_action').value || '$buyxgetnpercent' == $('rule_simple_action').value || '$buyxgetnfixdisc' == $('rule_simple_action').value ){
                    $('rule_actions_fieldset').up().select('h4')[0].update('".$this->__('Define X product (leave blank for any product)')."')
					$('rule_promo_sku').up().up().show();
					$('rule_promo_cats').up().up().show();
					$('rule_buy_x_get_n').up().up().show();
					$$(\"p[id='note_discount_step']\")[0].show();
					renameDiscountStepField('".$this->__('Number of X Products')."');
					removeOrSetValidatorClassName($('rule_buy_x_get_n'), ['validate-greater-than-zero', 'required-entry'], 'set');
					removeOrSetValidatorClassName($('rule_discount_step'), ['validate-greater-than-zero', 'required-entry'], 'set');
					getAmpromoNote(1);
					fieldsetChange('rule_y_product_fieldset', 'show');
					movingDomBetweenElements('rule_y_product_fieldset', 'rule_promo_sku');
					movingDomBetweenElements('rule_y_product_fieldset', 'rule_promo_cats');

				}
				if  ('ampromo_items' == $('rule_simple_action').value
                    || 'ampromo_cart' == $('rule_simple_action').value
                    || 'ampromo_product' == $('rule_simple_action').value
                    || 'ampromo_spent' == $('rule_simple_action').value
                    ){
                        $$(\"label[for='rule_discount_amount']\")[0].update(\"" . $strWithNewName . " \" + \"<span class='required'>*</span>\");
                    } 

                ampromo_note();
			}
			ampromo_hide();
			amrules_hint();
        ";
    }
}
