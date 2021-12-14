<?php
/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Feed
 */
class Merchante_MagetSync_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const TYPE_UPDATE = 1;
    const TYPE_INFO   = 2;

    const URL_UPDATES = 'https://secure.merchant-e.net/feeds-updates.xml';

    public function checkUpdate()
    {
        try {
            // load all new and relevant updates into inbox
            $feedData = array();
            $feedXml = $this->getFeedData();
            $version = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_version');
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = Mage::getSingleton('core/resource')->getTableName('adminnotification/inbox');
            if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
                foreach ($feedXml->channel->item as $item) {

                    $select = 'SELECT * FROM ' . $table . ' WHERE title =\''. (string)$item->title . '\' AND date_added =\'' . (string)$item->pubDate.'\'';
                    $query = $readConnection->fetchAll($select);
                    if ($query) {
                        continue;
                    }
                    $date = $this->getDate((string)$item->pubDate);
                    if ((int)$item->type == self::TYPE_UPDATE) {
                        $versionNew = (string)$item->version;
                        if ($version == $versionNew) {
                            continue;
                        }
                    }

                    $feedData[] = array(
                        'severity' => (int)$item->severity,
                        'date_added' => $this->getDate($date),
                        'title' => (string)$item->title,
                        'description' => (string)$item->description,
                        'url' => (string)$item->link,
                    );
                }
                if ($feedData) {
                    Mage::getModel('adminnotification/inbox')->parse($feedData);
                }
            }

            return $this;
        }catch (Exception $e)
        {
            //Mage::log("Error: ".print_r($e->getMessage(), true),null,'magetsync_feeds.log');
        }
    }

    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = self::URL_UPDATES;
        }
        $query = '?s=' . urlencode(Mage::getStoreConfig('web/unsecure/base_url'));
        return $this->_feedUrl  . $query;
    }

}