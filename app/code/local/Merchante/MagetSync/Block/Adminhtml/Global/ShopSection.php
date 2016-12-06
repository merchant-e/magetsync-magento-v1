<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for preparing Shop Section grid
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid container
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_global_shopSection';
        $this->_blockGroup = 'magetsync';
        $this->_headerText = Mage::helper('magetsync')->__('Shop Sections');
        $this->_addButtonLabel = Mage::helper('magetsync')->__('Add shop section (Category)');
        $url = $this->getUrl('*/*/sync', array('' => ''));
        $this->_addButton('sync_now', array(
            'label'     => Mage::helper('magetsync')->__('Sync Now'),
            'onclick'   =>  'setLocation(\''.$url.'\');',
            'class'     => 'save',
        ),0, 100);


        parent::__construct();
    }

}
