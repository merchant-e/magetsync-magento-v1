<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * 
 * Class Merchante_MagetSync_Model_Mysql4_Listing
 */
class Merchante_MagetSync_Model_Mysql4_Listing extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/listing', 'id');
    }
}