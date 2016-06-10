<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Recipient
 */
class Merchante_MagetSync_Model_Recipient extends Merchante_MagetSync_Model_Etsy
{
    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/recipient');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/recipient')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>'Please Select'));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['name'],'label'=>$CList['display']);
        }
        return $CArray;
    }

}