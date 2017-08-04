<?php
/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Helper_Data
 */
class Merchante_MagetSync_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * System configuration sections
     */
    const MAGETSYNC_SECTION = 'magetsync_section';
    const TEMPLATES_SECTION = 'magetsync_section_templates';
    const DRAFTMODE_SECTION = 'magetsync_section_draftmode';
    const PAYMENT_SECTION   = 'payment';

    /**
     * Config values
     *
     * @var array
     */
    protected $configSectionsData = [];


    /**
     * Get sections config
     *
     * @param   string $sectionName
     * @param   string $groupName
     * @param   string $fieldName
     * @param   string $storeId
     * @return  string
     */
    protected function getSectionsConfigValue($sectionName, $groupName, $fieldName, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()
                           ->getWebsite(true)
                           ->getDefaultGroup()
                           ->getDefaultStoreId();
        }

        if (
            ! isset($this->configSectionsData[$sectionName][$storeId]) ||
            null === $this->configSectionsData[$sectionName][$storeId]
        ) {
            $this->configSectionsData[$sectionName][$storeId]
                = Mage::getStoreConfig($sectionName, $storeId);
        }

        $result = $this->configSectionsData[$sectionName][$storeId][$groupName][$fieldName] ?: '';

        return $result;
    }

    /**
     * Get config values from magetsync section
     *
     * @param   string $groupName
     * @param   string $fieldName
     * @param   string $storeId
     * @return  string
     */
    protected function getMagetSyncSectionValue($groupName, $fieldName, $storeId = null)
    {
        return $this->getSectionsConfigValue(self::MAGETSYNC_SECTION, $groupName, $fieldName, $storeId);
    }

    /**
     * Get config values from Draft Mode section
     *
     * @param   string $groupName
     * @param   string $fieldName
     * @param   string $storeId
     * @return  string
     */
    protected function getDraftmodeSectionValue($groupName, $fieldName, $storeId = null)
    {
        return $this->getSectionsConfigValue(self::DRAFTMODE_SECTION, $groupName, $fieldName, $storeId);
    }

    /**
     * Get config values from Template section
     *
     * @param   string $groupName
     * @param   string $fieldName
     * @param   string $storeId
     * @return  string
     */
    protected function getTemplateSectionValue($groupName, $fieldName, $storeId = null)
    {
        return $this->getSectionsConfigValue(self::TEMPLATES_SECTION, $groupName, $fieldName, $storeId);
    }

    /**
     * Get config value from Payment section
     *
     * @param   string $groupName
     * @param   string $fieldName
     * @param   string $storeId
     * @return  string
     */
    protected function getPaymentSectionValue($groupName, $fieldName, $storeId = null)
    {
        return $this->getSectionsConfigValue(self::PAYMENT_SECTION, $groupName, $fieldName, $storeId);
    }

    /**
     * Get customer token
     *
     * @return string
     */
    public function getCustomerToken()
    {
       return $this->getMagetSyncSectionValue('magetsync_group', 'magetsync_field_tokencustomer');
    }

    /**
     * Get shop language
     *
     * @param null $storeId
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getMagetSyncLanguage($storeId = null)
    {
        return $this->getMagetSyncSectionValue('magetsync_group', 'magetsync_field_language', $storeId);
    }

    /**
     * Is logger enabled
     *
     * @return bool
     */
    public function isLogEnabled()
    {
        return (bool) $this->getMagetSyncSectionValue('magetsync_group_debug', 'enable_log');
    }

    /**
     * Is exception log enable
     *
     * @return bool
     */
    public function isLogExceptionEnabled()
    {
        return (bool) $this->getMagetSyncSectionValue('magetsync_group_debug', 'enable_exception_log');
    }

    /**
     * Is draft mode enable
     *
     * @return bool
     */
    public function isListingDraftMode()
    {
        return (bool) $this->getDraftmodeSectionValue('magetsync_group_draft','magetsync_field_listing_draft_mode');
    }

}