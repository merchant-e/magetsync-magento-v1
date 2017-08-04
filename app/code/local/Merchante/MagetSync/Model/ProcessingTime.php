<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_ProcessingTime
 */
class Merchante_MagetSync_Model_ProcessingTime extends Merchante_MagetSync_Model_Etsy
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/processingTime');
    }

    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/processingTime')->getCollection();
        $PArray = array(array('value'=>null, 'label'=>'Ready to ship in'));
        foreach ($Collection as $PList){

            $PArray[] = array('value'=>$PList['id'],'label'=>$PList['label']);
        }
        $PArray[] = array('value'=>-2, 'label'=>'Custom Range');
        return $PArray;
    }

}