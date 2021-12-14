<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Edit_Tabs
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('shippingtemplate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('magetsync')->__('Shipping Profiles'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab('shippingtemplate_form_section', array(
            'label' => Mage::helper('magetsync')->__('Shipping Profiles'),
            'title' => Mage::helper('magetsync')->__('Shipping Profiles'),
            'content' => $this->getLayout()
                    ->createBlock('magetsync/adminhtml_global_shippingTemplate_edit_tab_form')
                    ->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}