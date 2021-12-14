<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_ShopSection
 */
class Merchante_MagetSync_Model_ShopSection extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'ShopSection';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/shopSection');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/shopSection')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>Mage::helper('magetsync')->__('Please Select')));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['shop_section_id'],'label'=>$CList['title']);
        }
        return $CArray;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllShopSections($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function createShopSection($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateShopSection($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function deleteShopSection($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }
}