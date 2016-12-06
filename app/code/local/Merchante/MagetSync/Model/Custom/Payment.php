<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Custom_Payment
 */
class Merchante_MagetSync_Model_Custom_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'magetsync_payment';

    protected $_isInitializeNeeded = false;
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = true;
    protected $_canOrder = true;
    protected $_canUseCheckout = false;


}

?>