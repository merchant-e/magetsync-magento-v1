<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Mysql4_AttributeTemplate
 */
class Merchante_MagetSync_Model_Mysql4_AttributeTemplate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magetsync/attributeTemplate', 'id');
    }
}