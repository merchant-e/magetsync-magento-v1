<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class Merchante_MagetSync_Model_AttributeTemplate
 */
class Merchante_MagetSync_Model_AttributeTemplate extends Mage_Core_Model_Abstract
 {
    /**
     *
     */
    public function _construct()
   	{
        parent::_construct();
        $this->_init('magetsync/attributeTemplate');
   	}
}