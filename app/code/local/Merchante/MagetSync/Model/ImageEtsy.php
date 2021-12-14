<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_ImageEtsy
 */
class Merchante_MagetSync_Model_ImageEtsy extends Merchante_MagetSync_Model_Etsy
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/imageEtsy');
    }
}