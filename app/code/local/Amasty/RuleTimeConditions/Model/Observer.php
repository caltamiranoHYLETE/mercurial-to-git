<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RuleTimeConditions
 */


class Amasty_RuleTimeConditions_Model_Observer
{
    public function handleFormTimeCreation($observer)
    {
        /** @var Amasty_Rules_Helper_Data $helper */
        $helper = Mage::helper('amruletimeconditions');

        $currentPromoQuoteRule = Mage::registry('current_promo_quote_rule');
        $data = $currentPromoQuoteRule->getData();

        if (key_exists('amrule_from_time', $data) && is_array($data['amrule_from_time'])) {
            $data['amrule_from_time'] = implode(':', $data['amrule_from_time']);
        }

        if (key_exists('amrule_to_time', $data) && is_array($data['amrule_to_time'])) {
            $data['amrule_to_time'] = implode(':', $data['amrule_to_time']);
        }

        $fromTime = isset($data['amrule_from_time']) ? str_replace(':', ',', $data['amrule_from_time']) : '00,00,00';
        $toTime = isset($data['amrule_to_time']) ? str_replace(':', ',', $data['amrule_to_time']) : '00,00,00';

        $daysOfWeek = (isset($data['amrule_days_of_week']) && is_string($data['amrule_days_of_week'])) ? $data['amrule_days_of_week'] : '';

        /** @var Varien_Data_Form $form */
        $form = $observer->getForm();
        $actionFieldset = $form->getElement('base_fieldset');

        $showTimeStr = $helper->__('Use Time Condition');
        $valueUseTime = isset($data['amrule_use_time']) ? $data['amrule_use_time'] : '0';
        $fieldUseTime = $actionFieldset->addField('amrule_use_time', 'select', array(
            'label' => $showTimeStr,
            'title' => $showTimeStr,
            'name' => 'amrule_use_time',
            'options' => array(
                '1' => $helper->__('Yes'),
                '0' => $helper->__('No'),
            ),
            'value' => $valueUseTime
        ),
            'to_date'
        );

        $fromTimeStr = $helper->__('From time');
        $fieldFromTime = $actionFieldset
            ->addField('amrule_from_time', 'time', array(
                    'name' => 'amrule_from_time',
                    'label' => $fromTimeStr,
                    'id' => 'amrule_from_time',
                    'title' => $fromTimeStr,
                    'value' => $fromTime,
                ),
                'amrule_use_time'
            );

        $toTimeStr = $helper->__('To time');
        $fieldToTime = $actionFieldset
            ->addField('amrule_to_datetime', 'time', array(
                    'name' => 'amrule_to_time',
                    'label' => $toTimeStr,
                    'id' => 'amrule_to_time',
                    'title' => $toTimeStr,
                    'value' => $toTime,
                ),
                'amrule_from_time'
            );

        $showWeekStr = $helper->__('Use Weekdays Condition');
        $valueUseTime = isset($data['amrule_use_weekdays']) ? $data['amrule_use_weekdays'] : '0';
        $fieldUseWeekdays = $actionFieldset
            ->addField('amrule_use_weekdays', 'select', array(
                'label' => $showWeekStr,
                'title' => $showWeekStr,
                'name' => 'amrule_use_weekdays',
                'options' => array(
                    '1' => $helper->__('Yes'),
                    '0' => $helper->__('No'),
                ),
                'value' => $valueUseTime
            ),
                'amrule_to_datetime'
            );

        $dayOfWeekStr = $helper->__('Day(s) of week');
        $weekValues = Mage::app()->getLocale()->getOptionWeekdays();
        $fieldDayOfWeek = $actionFieldset
            ->addField('amrule_days_of_week', 'multiselect', array(
                    'name' => 'amrule_days_of_week[]',
                    'label' => $dayOfWeekStr,
                    'title' => $dayOfWeekStr,
                    'values' => $weekValues,
                    'value' => explode(',', $daysOfWeek)
                ),
                'amrule_use_weekdays'
            );

        /** @var Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Main $parent */
        $parent = $form->getParent();
        /** @var Mage_Adminhtml_Block_Widget_Form_Element_Dependence $child */
        $child = $parent->getChild('form_after');
        $child
            ->addFieldMap($fieldUseWeekdays->getHtmlId(), $fieldUseWeekdays->getName())
            ->addFieldMap($fieldDayOfWeek->getHtmlId(), $fieldDayOfWeek->getName())
            ->addFieldMap($fieldToTime->getHtmlId(), $fieldToTime->getName())
            ->addFieldMap($fieldFromTime->getHtmlId(), $fieldFromTime->getName())
            ->addFieldMap($fieldUseTime->getHtmlId(), $fieldUseTime->getName())
            ->addFieldDependence(
                $fieldDayOfWeek->getName(),
                $fieldUseWeekdays->getName(),
                '1'
            )
            ->addFieldDependence(
                $fieldToTime->getName(),
                $fieldUseTime->getName(),
                '1'
            )
            ->addFieldDependence(
                $fieldFromTime->getName(),
                $fieldUseTime->getName(),
                '1'
            );
    }

    public function saveBefore($observer)
    {
        $rule = $observer->getRule();
        $daysOfWeek = Mage::app()->getRequest()->get('amrule_days_of_week');
        if (is_array($daysOfWeek)) {
            $daysOfWeek = implode(',', $daysOfWeek);
        }
        $rule->setAmruleDaysOfWeek($daysOfWeek);
        $amruleFromTime = is_array($rule->getAmruleFromTime()) ? implode(':', $rule->getAmruleFromTime()) : $rule->getAmruleFromTime();
        $rule->setAmruleFromTime($amruleFromTime);
        $amruleToTime = is_array($rule->getAmruleToTime()) ? implode(':', $rule->getAmruleToTime()) : $rule->getAmruleToTime();
        $rule->setAmruleToTime($amruleToTime);
    }
}
