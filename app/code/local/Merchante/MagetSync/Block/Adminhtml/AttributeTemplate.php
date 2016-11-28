<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid container
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_attributeTemplate';
        $this->_blockGroup = 'magetsync';
        $this->_headerText = Mage::helper('magetsync')->__('Attribute Templates');
        $this->_addButtonLabel = Mage::helper('magetsync')->__('Add New Template');
        
        parent::__construct();
    }

    /**
     * Adds class to template grid
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-categories ' . parent::getHeaderCssClass();
    }
}
