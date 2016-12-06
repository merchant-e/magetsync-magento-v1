<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Shop
 */
class Merchante_MagetSync_Model_Shop extends Merchante_MagetSync_Model_Etsy
 {
     public function _construct()
     {
         parent::_construct();
         $this->_init('magetsync/shop');
     }
 }