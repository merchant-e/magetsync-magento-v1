<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Receipt
 */
class Merchante_MagetSync_Model_Receipt extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'Receipt';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/receipt');
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateReceipt($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllUserBuyerReceipts($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllShopReceipts($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getReceipt($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllUserBuyerTransactions($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function submitTracking($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

}