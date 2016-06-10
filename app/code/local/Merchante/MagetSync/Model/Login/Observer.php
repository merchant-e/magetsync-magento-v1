<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Login_Observer
 */
class Merchante_MagetSync_Model_Login_Observer
{

    /**
     * @param $observer
     */
    public function check($observer)
    {
        try
        {
          Mage::getModel('magetsync/feed')->checkUpdate();

        }catch (Exception $e)
        {
            //Mage::log("Error: ".print_r($e->getMessage(), true),null,'feeds.log');
        }
    }

}