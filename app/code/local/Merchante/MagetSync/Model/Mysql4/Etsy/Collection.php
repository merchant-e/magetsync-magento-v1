<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Mysql4_Etsy_Collection
 */
class Merchante_MagetSync_Model_Mysql4_Etsy_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
 {
     public function _construct()
     {
         $this->_init('magetsync/etsy');
     }
}