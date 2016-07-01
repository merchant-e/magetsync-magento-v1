<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_MagentoStore_Source
 */
class Merchante_MagetSync_Model_MagentoStore_Source
{
    /**
     * Method for loading Magento Store
     * values in system's drop down
     * @return array
     */
    public function toOptionArray()
    {
        Mage::app()->getStore()->resetConfig();
        $allStores = Mage::app()->getStores();
        $return = array();
        $return[] = array('value'=> null,'label' => Mage::helper('magetsync')->__('Please Select'));
        foreach ($allStores as $_eachStoreId => $val)
        {
            $_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
            $_storeName = Mage::app()->getStore($_eachStoreId)->getName();
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $return[] = array('value' => $_storeId, 'label' => $_storeName.' / '.$_storeCode);
        }

        return $return;
    }

}