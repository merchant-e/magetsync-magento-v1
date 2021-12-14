<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_WhenMade
 */
class Merchante_MagetSync_Model_WhenMade extends Merchante_MagetSync_Model_Etsy
{
    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/whenMade');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/whenMade')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>'Select when'));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['name'],'label'=>$CList['display']);
        }
        return $CArray;
    }

}