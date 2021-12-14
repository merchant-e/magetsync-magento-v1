<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Occasion
 */
class Merchante_MagetSync_Model_Occasion extends Merchante_MagetSync_Model_Etsy
{
    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/occasion');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/occasion')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>Mage::helper('magetsync')->__('Please Select')));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['name'],'label'=>$CList['display']);
        }
        return $CArray;
    }

}