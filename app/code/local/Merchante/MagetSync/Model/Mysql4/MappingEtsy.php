<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 * 
 * Class Merchante_MagetSync_Model_Mysql4_MappingEtsy
 */
class Merchante_MagetSync_Model_Mysql4_MappingEtsy extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/mappingEtsy', 'id');
    }
}