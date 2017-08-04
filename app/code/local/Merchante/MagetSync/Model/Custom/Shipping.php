<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Custom_Shipping
 */

class Merchante_MagetSync_Model_Custom_Shipping
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'magetsync_shipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $shippingData = Mage::helper('magetsync/data')->getValue('shipping_magetsync_data');

        if (!$shippingData) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);


        $title = Mage::getStoreConfig('carriers/magetsync_shipping/title');
        $methodName= Mage::getStoreConfig('carriers/magetsync_shipping/methods');

        $method->setCarrierTitle($title);//$shippingData['carrier_title'];
        $method->setMethodTitle($methodName);

        $method->setCost($shippingData['shipping_price']);
        $method->setPrice($shippingData['shipping_price']);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array();
    }

}