<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * 
 * Class Merchante_MagetSync_Model_Mysql4_Style
 */
class Merchante_MagetSync_Model_Mysql4_Style extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/style', 'id');
    }
}