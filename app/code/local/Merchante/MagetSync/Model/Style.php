<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Style
 */
class Merchante_MagetSync_Model_Style extends Merchante_MagetSync_Model_Etsy
{

    /**
     * @var string
     */
    public $name = 'Style';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/style');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/style')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>Mage::helper('magetsync')->__('Please Select')));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['style'],'label'=>$CList['style']);
        }
        return $CArray;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findSuggestedStyles($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

}