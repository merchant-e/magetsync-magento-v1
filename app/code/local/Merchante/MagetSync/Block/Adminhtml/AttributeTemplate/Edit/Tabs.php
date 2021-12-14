<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tabs
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize tabs
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('magetsync_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('magetsync')->__('Attribute Template Editor'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => $this->__('Attribute Template Editor'),
            'title' => $this->__('Attribute Template Editor'),
            'content' => $this->getLayout()
                    ->createBlock('magetsync/adminhtml_attributeTemplate_edit_tab_form')
                    ->toHtml()
        ));

        $this->addTab('products_section', array(
            'label'     => $this->__('Associated Products'),
            'title'     => $this->__('Associated Products'),
            'url'       => $this->getUrl('*/*/productstab', array('_current' => true)),
            'class'     => 'ajax'
        ));
        return parent::_beforeToHtml();
    }
}