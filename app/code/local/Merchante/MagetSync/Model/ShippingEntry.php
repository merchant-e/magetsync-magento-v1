<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_ShippingEntry
 */
class Merchante_MagetSync_Model_ShippingEntry extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'ShippingEntry';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/shippingEntry');
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function createShippingTemplateEntry($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getShippingTemplateEntry($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateShippingTemplateEntry($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function deleteShippingTemplateEntry($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }
}