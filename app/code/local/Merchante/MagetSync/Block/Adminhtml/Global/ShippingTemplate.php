<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for prepare Shipping Template grid
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid container
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_global_shippingTemplate';
        $this->_blockGroup = 'magetsync';
        $this->_headerText = Mage::helper('magetsync')->__('Shipping Profiles');
        $this->_addButtonLabel = Mage::helper('magetsync')->__('Add a shipping profile');
        $url = $this->getUrl('*/*/sync', array('' => ''));
        $this->_addButton('sync_now', array(
            'label'     => Mage::helper('magetsync')->__('Sync Now'),
            'onclick'   =>  'setLocation(\''.$url.'\');',
            'class'     => 'save',
        ),0, 100);
        parent::__construct();
    }

}
