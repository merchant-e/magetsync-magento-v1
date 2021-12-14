<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Edit_Tabs
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('shopsection_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Shop Sections'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab('shopsection_form_section', array(
            'label' => Mage::helper('magetsync')->__('Shop Sections'),
            'title' => Mage::helper('magetsync')->__('Shop Sections'),
            'content' => $this->getLayout()
                    ->createBlock('magetsync/adminhtml_global_shopSection_edit_tab_form')
                    ->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}