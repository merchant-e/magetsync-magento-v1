<?php
/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Helper_Data
 */
class Merchante_MagetSync_Helper_Config extends Mage_Core_Helper_Abstract
{
    const PREFIX = '';

    protected $config;

    public function getCustomerToken()
    {
       return Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getMagetSyncLanguage($storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()
                           ->getWebsite(true)
                           ->getDefaultGroup()
                           ->getDefaultStoreId();
        }

        $language = Mage::getStoreConfig(
            'magetsync_section/magetsync_group/magetsync_field_language', $storeId
        );

        return $language;

    }


}