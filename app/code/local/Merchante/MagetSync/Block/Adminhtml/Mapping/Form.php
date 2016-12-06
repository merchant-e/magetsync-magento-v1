<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for enabled edit actions on Listing form
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Edit
 */
class Merchante_MagetSync_Block_Adminhtml_Mapping_Form extends
    Mage_Adminhtml_Block_Widget_Form_Container{

    /**
     * Initialize form container
     */
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_controller  = 'adminhtml_mapping';
        $this->_blockGroup  = 'magetsync';
        $this->_headerText  = Mage::helper('magetsync')->__('MagetSync Mappings (For existing Etsy stores)');
        $this->_mode        = 'edit';

        parent::__construct();
        $this->setTemplate('magetsync/mapping.phtml');

    }

    /**
     * Update header text in edit form
     * @return string
     */
    public function getHeaderText()
    {
         return Mage::helper('magetsync')->__('MagetSync Mappings (For existing Etsy stores)');
    }

}