<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_OrderEtsy
 */
class Merchante_MagetSync_Model_OrderEtsy extends Merchante_MagetSync_Model_Etsy
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/orderEtsy');
    }

}