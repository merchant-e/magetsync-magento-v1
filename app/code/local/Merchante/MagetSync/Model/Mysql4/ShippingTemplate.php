<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Mysql4_ShippingTemplate
 */
class Merchante_MagetSync_Model_Mysql4_ShippingTemplate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/shippingTemplate', 'id');
    }
}