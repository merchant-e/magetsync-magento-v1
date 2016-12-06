<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * 
 * Class Merchante_MagetSync_Model_Mysql4_Category
 */
class Merchante_MagetSync_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/category', 'id');
    }
}