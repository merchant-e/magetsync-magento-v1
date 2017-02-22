<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Variation
 */
class Merchante_MagetSync_Model_Variation extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'Variation';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/variation');
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function createListingVariation($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function createListingVariations($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getListingVariations($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function deleteListingVariation($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateListingVariation($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateInventory($obligatory, $params = null)
    {
        /**
         * TODO move to new model after API will be set up
         */
        return $this->selectExecute('ListingInventory',__FUNCTION__,$obligatory,$params);
    }
}