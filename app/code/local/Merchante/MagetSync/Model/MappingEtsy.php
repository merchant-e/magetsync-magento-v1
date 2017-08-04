<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_MappingEtsy
 */
class Merchante_MagetSync_Model_MappingEtsy extends Merchante_MagetSync_Model_Etsy
 {
     /**
     *
     */
    public function _construct()
     {
         parent::_construct();
         $this->_init('magetsync/mappingEtsy');
     }

 }