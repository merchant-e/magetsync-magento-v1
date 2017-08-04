<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_Listing
 */
class Merchante_MagetSync_Block_Adminhtml_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize grid container
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_listing';
        $this->_blockGroup = 'magetsync';
        $this->_headerText = Mage::helper('magetsync')->__('Listings Manager');

        parent::__construct();
        $this->_removeButton('add');

     }
}
