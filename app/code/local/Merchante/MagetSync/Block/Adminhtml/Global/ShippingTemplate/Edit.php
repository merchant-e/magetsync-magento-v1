<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for enabling edit actions on Shipping Template form
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Edit
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Edit extends
    Mage_Adminhtml_Block_Widget_Form_Container{

    /**
     * Initialize form container
     */
    public function __construct()
    {
        parent::__construct();
        /** @var  _objectId primary key*/
        $this->_objectId = 'id';
        /** @var  _blockGroup module name*/
        $this->_blockGroup = 'magetsync';
        /** @var  _controller controller name */
        $this->_controller = 'adminhtml_global_shippingTemplate';
        $this->_updateButton('save', 'label', Mage::helper('magetsync')->__('Save shipping profile'));
        $this->_updateButton('delete', 'label', Mage::helper('magetsync')->__('Delete shipping profile'));
    }

    /**
     * Update header text in edit form
     * @return string
     */
    public function getHeaderText()
    {
        if( Mage::registry('magetsync_shippingtemplate')&&Mage::registry('magetsync_shippingtemplate')->getId())
        {
            return Mage::helper('magetsync')->__('Edit Shipping Profile')." ".$this->htmlEscape(
                Mage::registry('magetsync_shippingtemplate')->getTitle()).'<br />';
        }
        else
        {
            return Mage::helper('magetsync')->__('Add a shipping profile');
        }
    }
}