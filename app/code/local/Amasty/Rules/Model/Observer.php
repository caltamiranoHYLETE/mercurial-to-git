<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */
class Amasty_Rules_Model_Observer
{
    const BASED_ON_BIGGEST  = 3;
    const BASED_ON_ORIGINAL = 2;
    const BASED_ON_PREVIOUS = 1;
    const BASED_ON_PRICE    = 0;
    const EQUALS = 0;
    const CONTAINS = 1;
    const ACTIVE = 1;
    const INSTOCK = 1;
    
    const Y_PRODUCT_FIELDSET_NAME = 'y_product_fieldset';
    
    /*
    * array with all actions that contains field "Promo Items"
    * */
    protected $_arrayWithSimpleAction = array(
                        'buy_x_get_y_percent',
                        'buy_x_get_y_fixdisc',
                        'buy_x_get_y_fixed',
                        'buy_x_get_n_percent',
                        'buy_x_get_n_fixdisc',
                        'buy_x_get_n_fixed',
                        'setof_percent',
                        'setof_fixed',
                        );
    /*
    * array with all actions for "Product Set"
    * */
    protected $_arrayWithProductSet = array('setof_percent', 'setof_fixed');

    /**
     * @var bool
     */
    protected $stopFurtherRules = false;

    /**
     * @var bool
     */
    protected $currentItem = false;

    /**
     * @var bool
     */
    protected $rules = false;

    /**
     * Process sales rule form creation
     *
     * @param   Varien_Event_Observer $observer
     * @return $this
     */
    public function handleFormCreation($observer)
    {
        $actionsSelect = $observer->getForm()->getElement('simple_action');
        if ($actionsSelect) {
            $defaultTypes = $actionsSelect->getValues();
            $defaultTypes[3]['label'] = Mage::helper('amrules')->__('Buy N products, and get next same products free.');
            $defaultGroupedTypes      = array(
                0 => array(
                    'label' => 'Default',
                    'value' => $defaultTypes,
                ),
            );

            $actionsSelect->setValues(
                array_merge(
                    $defaultGroupedTypes,
                    Mage::helper('amrules')->getDiscountTypes()
                )
            );

            $actionsSelect->setOnchange('ampromo_hide();');
        }

        $actionsSelect = $observer->getForm()->getElement('discount_step');
        if ($actionsSelect) {
            $actionsSelect->setData('class', 'validate-greater-than-zero');
            $actionsSelect->setData('note', Mage::helper('amrules')
                ->__('For the rule `Buy <b>2 X</b>, get 5 Y` it is 2  '));
        }


        $fldSet = $observer->getForm()->getElement('action_fieldset');
        $actionsFldSet = $observer->getForm()->getElement('actions_fieldset');
        $actionsFldSet->setData(
            'legend',
            Mage::helper('amrules')->__('Apply the rule to the following products only (leave blank for all products)')
        );
        if ($fldSet) {
            if ('true' != (string)Mage::getConfig()->getNode('modules/Amasty_Promo/active')) {
                $fldSet->addField(
                    'promo_sku', 'text', array(
                    'name'  => 'promo_sku',
                    'label' => Mage::helper('amrules')->__('Promo Items'),
                    'note'  => Mage::helper('amrules')->__('Comma separated list of the SKUs'),
                ),
                    'discount_amount'
                );
            }

            $fldSet->addField(
                'promo_cats', 'text', array(
                'name'  => 'promo_cats',
                'label' => Mage::helper('amrules')->__('Promo Categories'),
                'note'  => Mage::helper('amrules')->__('Comma separated list of the category ids'),
            ),
                'discount_amount'
            );

            $fldSet->addField(
                'each_m', 'text', array(
                'name'  => 'each_m',
                'label' => Mage::helper('amrules')->__('Each Product (step)'),
                'note'  => Mage::helper('amrules')
                    ->__('Set <b>1</b> for all products, <b>2</b> for 1-th, 3-th, 5-th, and so on.'),
                'class' => 'validate-greater-than-zero'
            ),
                'discount_amount'
            );
            $fldSet->addField(
                'buy_x_get_n', 'text', array(
                'name'  => 'buy_x_get_n',
                'label' => Mage::helper('amrules')->__('Number of Y Products'),
                'note' => Mage::helper('amrules')->__('For the rule `Buy 2 X, get <b>5 Y</b>` it is 5'),
                'class' => 'validate-greater-than-zero'
            ),
                'discount_amount'
            );

            $fldSet->addField(
                'max_discount', 'text', array(
                'name'  => 'max_discount',
                'label' => Mage::helper('amrules')->__('Max Amount of Discount'),
            ),
                'discount_amount'
            );


            $priceSelector = $fldSet->addField('price_selector', 'select', array(
                'label'   => Mage::helper('amrules')->__('Calculate Discount Based On'),
                'title'   => Mage::helper('amrules')->__('Calculate Discount Based On'),
                'name'    => 'price_selector',
                'options' => array(
                    self::BASED_ON_PRICE => Mage::helper('amrules')->__('Price (Special Price if Set)'),
                    self::BASED_ON_PREVIOUS => Mage::helper('amrules')->__('Price After Previous Discount(s)'),
                    self::BASED_ON_ORIGINAL => Mage::helper('amrules')->__('Original Price'),
                    self::BASED_ON_BIGGEST => Mage::helper('amrules')->__('Original Price. Apply the Biggest Discount.'),
					4 => 'MSRP'
                ),
                'note'     => Mage::helper('amrules')->__('Biggest discount means additional discounts from catalog rules or special price are not summed up. We use the biggest one. Works with percent discounts only.'),
            ));
            $priceSelector->setOnchange('amrules_hint();');

            $fldSet->addField('amskip_rule', 'select', array(
                'label'   => Mage::helper('amrules')->__('Skip Items with Special Price'),
                'title'   => Mage::helper('amrules')->__('Skip Items with Special Price'),
                'name'    => 'amskip_rule',
                'options' => array(
                    '0' => Mage::helper('amrules')->__('As Default'),
                    '1' => Mage::helper('amrules')->__('Yes'),
                    '2' => Mage::helper('amrules')->__('No'),
                    '3' => Mage::helper('amrules')->__('Skip If Discounted'),
                ),
            ));

        }


        return $this;
    }

    public function handleFormCondition($observer)
    {
        $text = 'The rule goes into action only when the following cart conditions are met.';
        $transport = $observer->getTransport();
        if ($observer->getBlock()->getType() == "adminhtml/promo_quote_edit_tab_conditions") {
            $transport->setHtml(
                str_replace(
                    'Apply the rule only if the following conditions are met (leave blank for all products)',
                    Mage::helper('amrules')->__($text),
                    $transport->getHtml()
                )
            );
        }
    }


    /**
     * @param $observer
     * Process quote item validation and discount calculation
     *
     * @return $this
     */
    public function handleValidation($observer)
    {
        $promotions = Mage::getSingleton('amrules/promotions');
//        $this->setPreviousRules($observer->getQuote(), $observer->getRule());
//
//        if ((($this->currentItem == false) || ($this->currentItem->getItemId() != $observer->getItem()->getItemId()))) {
//            $this->stopFurtherRules = false;
//        }
//
//        if (!$this->stopFurtherRules && !$this->checkStopPreviousRule()) {
//            $this->setStopRuleAndItem($observer->getRule(), $observer->getItem());
            $promotions->process($observer);
//        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function checkStopPreviousRule()
    {
        $result = false;

        if ($this->rules->getSize()) {
            foreach ($this->rules as $item) {
                if (Mage::helper('amrules')->isStopRuleProcessing($item)) {
                    $result = true;
                }
            }
        }
        
        return $result;
    }


    /**
     * @param $quote
     * @return $this
     */
    protected function setPreviousRules($quote, $rule)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $customerGroupId = $quote->getCustomerGroupId();
        $couponCode = $quote->getCouponCode();
        $key = $websiteId . '_' . $quote->getCustomerGroupId() . '_' . $couponCode;

        $this->rules = Mage::getResourceModel('salesrule/rule_collection')
            ->setValidationFilter($websiteId, $customerGroupId, $couponCode)
            ->addFieldToFilter('sort_order', array('lt'=> $rule->getSortOrder()))
            ->load();
        
        return $this->rules;
    }

    /**
     * @param $rule
     * @return $this
     */
    protected function setStopRuleAndItem($rule, $item)
    {
        $this->stopFurtherRules = Mage::helper('amrules')->isStopRuleProcessing($rule);

        $this->currentItem = $item;

        return $this;
    }

    /**
     * Process sales rule before save
     *
     * @param   Varien_Event_Observer $observer
     */
    public function saveBefore($observer)
    {
        $controllerAction = $observer->getRule()->getData();
        $setof_percent    = Amasty_Rules_Helper_Data::TYPE_SETOF_PERCENT;
        $setof_fixed      = Amasty_Rules_Helper_Data::TYPE_SETOF_FIXED;

        if ($controllerAction['simple_action'] == $setof_percent || $controllerAction['simple_action'] == $setof_fixed) {
            $data                       = $observer->getRule()->getData();
            $r                          = array(
                'type'               => 'salesrule/rule_condition_product_combine',
                'attribute'          => null,
                'operator'           => null,
                'value'              => '1',
                'is_value_processed' => null,
                'aggregator'         => 'any',
                'conditions'         =>
                    array(
                        0 =>
                            array(
                                'type'               => 'salesrule/rule_condition_product',
                                'attribute'          => 'category_ids',
                                'operator'           => '()',
                                'value'              => $data['promo_cats'],
                                'is_value_processed' => false,
                            ),
                        1 =>
                            array(
                                'type'               => 'salesrule/rule_condition_product',
                                'attribute'          => 'quote_item_sku',
                                'operator'           => '()',
                                'value'              => $data['promo_sku'],
                                'is_value_processed' => false,
                            ),
                    ),
            );
            $itemsInSet                 = count(preg_split('@,@', $data['promo_cats'], null, PREG_SPLIT_NO_EMPTY))
                                          + count(preg_split('@,@', $data['promo_sku'], null, PREG_SPLIT_NO_EMPTY));
            $data['actions_serialized'] = serialize($r);
            $data['discount_step']      = $itemsInSet;

            $observer->getRule()->setData($data);
        }
    }

    /**
     * Process sales rule after save
     *
     * @param   Varien_Event_Observer $observer
     */
    public function saveAfter($observer)
    {
        $rule = $observer->getRule();

        $conditions = $rule->getConditions()->asArray();
        $unsafeIs   = 0;
        if (isset($conditions['conditions'])) {
            $unsafeIs = $this->checkForIs($conditions['conditions']);
        }

        $actions = $rule->getActions()->asArray();
        if (isset($actions['conditions']) && !$unsafeIs) {
            $unsafeIs = $this->checkForIs($actions['conditions']);
        }

        if (Mage::getStoreConfig('amrules/general/show_stock_warning')) {
            $skuArray          = array();
            $checkActionForSku = array(array(), array());
            $promoSku          = $rule->getPromoSku();
            $trimPromoSku        = trim($promoSku);
            if (!empty($trimPromoSku)
                && in_array($rule->getSimpleAction(), $this->_arrayWithSimpleAction)
            ) {
                $skuArray = $this->_checkActionForPromoSku($rule);
            }

            $actions[] = $conditions;
            if (!in_array($rule->getSimpleAction(), $this->_arrayWithProductSet)) {
                $checkActionForSku = $this->_checkActionForSku($actions);
            }
            if ($skuArray) {
                $checkActionForSku[self::EQUALS] = array_merge($checkActionForSku[self::EQUALS], $skuArray);
            }
            if (!empty($checkActionForSku[self::EQUALS])
                || !empty($checkActionForSku[self::CONTAINS])
            ) {
                $skuArray = $this->_checkForSku($checkActionForSku);
            }
            $getParamBack = Mage::app()->getRequest()->getParam('back');

            if ($skuArray && $rule->getIsActive() == self::ACTIVE && $getParamBack) {
                $this->_generateMessage($skuArray);
            }
        }

        if ($unsafeIs) {
            Mage::getSingleton('adminhtml/session')->addNotice('It is more safe to use `is one of` operator and not `is` for comparison.  Please correct if the rule does not work as expected.');
        }

        if (Mage::app()->getStore()->isAdmin()) {
            $this->_saveBannersInfo($rule);
        }
    }

    /**
     * check rules "Buy X Get Y", "Buy X Get N Of Y"
     *
     * @param $rule
     *
     * @return array
     */
    protected function _checkActionForPromoSku($rule)
    {
        $strWithSku    = $rule->getPromoSku();
        $actions       = $rule->getActions()->getData('actions');
        $outOfStockSku = array();
        foreach ($actions as $action) {
            if ($action['attribute'] == "quote_item_sku") {
                $strWithItemsSku = $action['value'];
                $outOfStockSku   = $this->_convertAndFormat($strWithItemsSku);
            }
        }

        if ($strWithSku != "") {
            $arrayWithSku  = $this->_convertAndFormat($strWithSku);
            $outOfStockSku = array_merge($outOfStockSku, $arrayWithSku);
        }

        return array_unique($outOfStockSku);
    }

    /**
     * check products in rules on QTY <= 0 OR stock status "In Stock"
     * return array with product's sku that have QTY <= 0 OR stock status "Out Of Stock"
     *
     * @param $skusFromRules
     *
     * @return array
     */
    protected function _checkForSku($skusFromRules)
    {
        $strWithLikeSkus = "";
        $strWithEqSkus   = "";
        $arrayWithEqSkus = array();

        $collectionWithProducts =
            Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->joinField(
                    'qty',
                    'cataloginventory/stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->joinField(
                    'use_config_manage_stock',
                    'cataloginventory/stock_item',
                    'use_config_manage_stock',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->joinField(
                    'stock_status',
                    'cataloginventory/stock_status',
                    'stock_status',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->addFieldToFilter('use_config_manage_stock', self::INSTOCK)
                ->addFieldToFilter('type_id', array('nin' => array('bundle', 'configurable', 'grouped')));
        /*
         * get arrays with sku
         * like not working with arrays
         * generate string with all likes
         * */
        if (!empty($skusFromRules[self::EQUALS])) {
            $strWithLikeSkus = "e.sku IN ('" . implode("', '", $skusFromRules[self::EQUALS]) . "')";
            if (!empty($skusFromRules[self::CONTAINS])) {
                $strWithLikeSkus .= " OR ";
            }
        }

        if (!empty($skusFromRules[self::CONTAINS])) {
            foreach ($skusFromRules[self::CONTAINS] as $skuFromRules) {
                $arrayWithEqSkus[] = "e.sku LIKE '%" . $skuFromRules . "%'";
            }
            $strWithEqSkus = implode($arrayWithEqSkus, " OR ");
        }

        $strWithResultQuery = $strWithLikeSkus . $strWithEqSkus;
        $collectionWithProducts->getSelect()
                                ->where($strWithResultQuery)
                                ->where("at_qty.qty <= 0 OR at_stock_status.stock_status <> 1")
                                ->order('sku', 'ASC');

        $resultArray = $collectionWithProducts->getData();

        return $resultArray;
    }

    /**
     * @param $rule
     */
    protected function _saveBannersInfo($rule)
    {
        $data         = $rule->getData();
        $bannersModel = Mage::getModel('amrules/banners');
        $id           = $data['rule_id'];
        $bannersModel = $bannersModel->loadByRuleId($id);

        $bannersModel->addData($data);
        $bannersModel->save();
    }

    protected function checkForIs($array)
    {
        foreach ($array as $element) {
            if ($element['operator'] == '==' && strpos($element['value'], ',') !== false) {
                return true;
            }
            if (isset($element['conditions'])) {
                $this->checkForIs($element['conditions']);
            }
        }

        return false;
    }

    /**
     * save images on server
     *
     * @param $observer
     */
    public function salesRulePrepareSave($observer)
    {
        $this->_saveRulesImage($observer->getRequest(), 'top_banner_img');
        $this->_saveRulesImage($observer->getRequest(), 'after_name_banner_img');
        $this->_saveRulesImage($observer->getRequest(), 'label_img');
    }

    /**
     * @param $request
     * @param $file
     */
    protected function _saveRulesImage($request, $file)
    {
        if ($data = $request->getPost()) {
            if (isset($data[$file]) && isset($data[$file]['delete'])) {
                $data[$file] = null;
            } else {
                if (isset($_FILES[$file]['name']) && $_FILES[$file]['name'] != '') {
                    $fileName    = Mage::helper("amrules/image")->upload($file);
                    $data[$file] = $fileName;
                } else {
                    if (isset($data[$file]['value'])) {
                        $data[$file] = basename($data[$file]['value']);
                    }
                }
            }

            $request->setPost($data);
        }
    }

    /**
     * add enctype = 'multipart/form-data'
     *
     * @param $observer
     */
    public function addEnctypeToForm($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Promo_Quote_Edit_Form) {
            $form = $observer->getBlock()->getForm();

            $form->setData('enctype', 'multipart/form-data');
            $form->setUseContainer(true);
        } elseif ($block instanceof Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Actions) {
            $form = $observer->getBlock()->getForm();

            $fieldset = $form->addFieldset(
                self::Y_PRODUCT_FIELDSET_NAME,
                array(
                    'legend' => Mage::helper('amrules')->__('Define Y product (X and Y are different products)')
                )
            );
        }
    }

    /**
     * check rules on shopping cart price rules(grid)
     * show message with product's sku that have QTY <= 0 OR stock status "Out Of Stock"
     *
     * @param $observer
     */
    public function prePromoQuoteIndex($observer)
    {
        if (Mage::getStoreConfig('amrules/general/show_stock_warning')) {
            $resultArray        = array();
            $actionsAndConditions = array();
            $rulesCollection    = Mage::getModel('salesrule/rule')
                                    ->getCollection()
                                    ->addFieldToSelect('actions_serialized')
                                    ->addFieldToSelect('conditions_serialized')
                                    ->addFieldToSelect('promo_sku')
                                    ->addFieldToSelect('simple_action')
                                    ->addFieldtoFilter('is_active', self::ACTIVE);

            $rulesData    = $rulesCollection->getData();
            $arrayWithSku = array();
            foreach ($rulesData as $rule) {
                if (isset($rule['promo_sku'])
                    && in_array($rule['simple_action'], $this->_arrayWithSimpleAction)
                ) {
                    $arrayWithSku = array_merge($arrayWithSku, $this->_convertAndFormat($rule['promo_sku']));
                }
                if (!in_array($rule['simple_action'], $this->_arrayWithProductSet)) {
                    $actionsAndConditions[] = unserialize($rule['actions_serialized']);
                    $actionsAndConditions[] = unserialize($rule['conditions_serialized']);
                }
            }
            $skuArray = $this->_checkActionForSku($actionsAndConditions, $arrayWithSku);
            if (!empty($skuArray[self::EQUALS])
                || !empty($skuArray[self::CONTAINS])
            ) {
                $resultArray = $this->_checkForSku($skuArray);
            }

            if ($resultArray) {
                $this->_generateMessage($resultArray);
            }
        }
    }

    /**
     * check sku in one rule and all rules in shopping cart price rules
     * generate message with sku
     *
     * @param $actionsAndConditions
     * @param null $promoSkus
     *
     * @return array
     */
    protected function _checkActionForSku($actionsAndConditions, $promoSkus = null)
    {
        $skus                = $this->_recGetArrayWithSkus($actionsAndConditions, 'value');
        $arrayWithOutOfStock = array(array(), array());
        /*
         * from recursive we get array(arrayWithOthers(), arrayWithLikes())
         * */
        if (!empty($skus)) {
            $count = count($skus);
            for ($i = 0; $i < $count; $i ++) {
                $skus[$i] = array_unique($skus[$i]);
                foreach ($skus[$i] as $sku) {
                    /*
                     * из рекурсии иногда может прийти "лишние" элементы-массивы
                     * эта проверка необходима для отсеивания их
                     * */
                    if (!is_array($sku)) {
                        $arrayWithOutOfStock[$i] = array_merge(
                            $arrayWithOutOfStock[$i],
                            $this->_convertAndFormat($sku)
                        );
                    }
                }
            }
        }
        if ($promoSkus) {
            $arrayWithOutOfStock[self::EQUALS] = array_merge($arrayWithOutOfStock[self::EQUALS], $promoSkus);
        }

        return $arrayWithOutOfStock;
    }

    /**
     * convert str to array
     * delete spaces in sku's array
     *
     * @param $sku
     *
     * @return array
     *
     */
    protected function _convertAndFormat($sku)
    {
        $skusFromRules = explode(',', $sku);
        $skusFromRules = array_map('trim', $skusFromRules);

        return $skusFromRules;
    }

    /**
     * generate message with product's sku that qty<=0 and out of stock
     *
     * @param $outOfStockSkus
     */
    protected function _generateMessage($outOfStockSkus)
    {
        $arrayWithLinks = array();
        if ($outOfStockSkus) {
            foreach ($outOfStockSkus as $outOfStockSku) {
                $url = Mage::helper('adminhtml')->getUrl('/catalog_product/edit',
                    array('id' => $outOfStockSku['entity_id']));
                $arrayWithLinks[] = '<a href="' . $url . '"target="_blank">' . $outOfStockSku['sku'] . '</a>';
            }
            $strWithLinks = implode($arrayWithLinks, ', ');
            if ($strWithLinks != "") {
                $message = Mage::helper('amrules')->__(
                    "Please notice, the %s have stock quantity <= 0 or are \"Out of stock\". That may interfere in proper rule execution.",
                    $strWithLinks);
                Mage::getSingleton('adminhtml/session')->addWarning($message);
            }
        }
    }


    /**
     * get skus from tree conditions and actions
     *
     * @param $conditions
     * @param $searchFor
     *
     * @return array
     */
    protected function _recGetArrayWithSkus($conditions, $searchFor)
    {
        static $arrayWithSku = array(array(), array());
        static $arrayWithEqualSkus = array();
        foreach ($conditions as $key => $condition) {
            if ($key == $searchFor
                && is_string($condition)
                && $condition != ""
            ) {
                if ($conditions['attribute'] == 'sku'
                    || $conditions['attribute'] == 'quote_item_sku'
                ) {
                    if ($conditions['operator'] == "{}"
                        || $conditions['operator'] == "!{}"
                    ) {
                        $arrayWithEqualSkus[] = $condition;
                    } else {
                        $arrayWithSku[self::EQUALS][] = $condition;
                    }
                }
            }
            if (is_array($conditions[$key])) {
                $this->_recGetArrayWithSkus($condition, $searchFor);
            }
        }
        if (!empty($arrayWithEqualSkus)) {
            $arrayWithSku[self::CONTAINS] = array_unique($arrayWithEqualSkus);
        }

        if (!empty($arrayWithSku[self::EQUALS])
            || !empty($arrayWithSku[self::CONTAINS])
        ) {
            return $arrayWithSku;
        }

        return null;
    }
}
