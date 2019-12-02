<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Observer
{
    /**
     * Observer for generate support link in system config page
     */
    public function addSupportBlock()
    {
        $section = Mage::app()->getRequest()->getParam('section');
        $sectionNode = Mage::app()->getConfig()->getNode('adminhtml/amasty_notifications/' . $section);
        if (!$sectionNode) {
            return;
        }

        $layout = Mage::app()->getLayout();
        $supportBlock = $layout->createBlock('ambase/adminhtml_support');
        $supportBlock->setContent($sectionNode->content);
        $supportBlock->setClass($sectionNode->style);
        $supportBlock->setTemplate('amasty/ambase/support.phtml');
        $contentBlock = '';
        foreach ($layout->getBlock('content')->getChild() as $content) {
            if (is_a($content, 'Mage_Adminhtml_Block_System_Config_Edit')) {
                $contentBlock = $content->getNameInLayout();
                break;
            }
        }

        $formBlock = $layout->getBlock($contentBlock);
        $listBlock = $layout->createBlock('core/text_list', 'amasty_form_container');
        $listBlock->append($supportBlock);
        $listBlock->append($formBlock->getChild('form'));
        $formBlock->setChild('form', $listBlock);
    }
}