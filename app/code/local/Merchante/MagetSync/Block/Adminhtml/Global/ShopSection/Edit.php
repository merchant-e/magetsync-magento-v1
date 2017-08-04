<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for enabling edit actions on Shop Section form
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Edit
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Edit extends
    Mage_Adminhtml_Block_Widget_Form_Container{

    /**
     * Initialize form container
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        /** @var  _objectId primary key*/
        $this->_blockGroup = 'magetsync';
        /** @var  _blockGroup module name*/
        $this->_controller = 'adminhtml_global_shopSection';
        /** @var  _controller controller name */
        $this->_updateButton('save', 'label', Mage::helper('magetsync')->__('Save shop section'));
        $this->_updateButton('delete', 'label', Mage::helper('magetsync')->__('Delete shop section'));
    }

    /**
     * Update header text in edit form
     * @return string
     */
    public function getHeaderText()
    {
        if( Mage::registry('magetsync_shopsection')&&Mage::registry('magetsync_shopsection')->getId())
        {
            return Mage::helper('magetsync')->__('Edit Shop Section')." ".$this->htmlEscape(
                Mage::registry('magetsync_shopsection')->getTitle()).'<br />';
        }
        else
        {
            return Mage::helper('magetsync')->__('Add shop section (Category)');
        }
    }
}