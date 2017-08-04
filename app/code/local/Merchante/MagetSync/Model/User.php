<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_User
 */
class Merchante_MagetSync_Model_User extends Merchante_MagetSync_Model_Etsy
 {

    public $name = 'User';

     public function _construct()
     {
         parent::_construct();
         $this->_init('magetsync/user');
     }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getUser($obligatory, $params = null)
    {
        return $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
    }

 }