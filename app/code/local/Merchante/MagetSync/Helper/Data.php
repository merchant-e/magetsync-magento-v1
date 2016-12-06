<?php
/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Helper_Data
 */
class Merchante_MagetSync_Helper_Data extends Mage_Core_Helper_Abstract{

    public function getValue($key)
    {
        return Mage::registry($key);
    }

    public function setValue($key, $value)
    {
        Mage::register($key,$value);
    }

    public function unsetValue($key)
    {
        Mage::unregister($key);
    }

    /**
     * @param $to
     * @param $amt
     * @return bool|float
     */
    public function convertValue($to, $amt)
    {
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();

        /** @var [] $rates */
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));

        if($rates && $rates[$to] == 0) {
            return false;
        }

        $amt = $amt / $rates[$to];

        return $amt;
    }

}
