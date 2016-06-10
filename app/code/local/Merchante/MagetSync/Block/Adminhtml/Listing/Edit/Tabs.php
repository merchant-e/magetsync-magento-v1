<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Tabs
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('magetsync_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('magetsync')->__('Listing Editor'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => $this->__('Listing Editor'),
            'title' => $this->__('Listing Editor'),
            'content' => $this->getLayout()
                    ->createBlock('magetsync/adminhtml_listing_edit_tab_form')
                    ->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}