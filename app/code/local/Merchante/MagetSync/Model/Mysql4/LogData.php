<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * 
 * Class Merchante_MagetSync_Model_Mysql4_LogData
 */
class Merchante_MagetSync_Model_Mysql4_LogData extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/logData', 'id');
    }
}