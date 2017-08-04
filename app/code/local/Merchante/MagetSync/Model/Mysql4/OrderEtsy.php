<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 * 
 * Class Merchante_MagetSync_Model_Mysql4_OrderEtsy
 */
class Merchante_MagetSync_Model_Mysql4_OrderEtsy extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/orderEtsy', 'order_etsy_id');
    }
}