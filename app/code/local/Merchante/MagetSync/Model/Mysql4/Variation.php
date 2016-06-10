<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Mysql4_Variation
 */
class Merchante_MagetSync_Model_Mysql4_Variation extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/variation', 'id');
    }
}