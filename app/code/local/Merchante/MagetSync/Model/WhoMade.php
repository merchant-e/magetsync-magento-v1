<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_WhoMade
 */
class Merchante_MagetSync_Model_WhoMade extends Merchante_MagetSync_Model_Etsy
{
    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/whoMade');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/whoMade')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>'Select a maker'));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['name'],'label'=>$CList['display']);
        }
        return $CArray;
    }

}