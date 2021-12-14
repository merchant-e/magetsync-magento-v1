<?php
/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


/**
 * Changes for magetsync_whenmade table
 */
$changes = array(
    '2010_2016'   => array('name' => '2010_2021','display' => '2010 - 2021')
);

$whens = Mage::getModel('magetsync/whenMade')
    ->getCollection();

foreach ($whens as $when) {

    $key = $when->getData('name');
    if (array_key_exists($key, $changes)) {
        $when->setName($changes[$key]['name'])
            ->setDisplay($changes[$key]['display'])
            ->save();
    }
}

$installer->endSetup();