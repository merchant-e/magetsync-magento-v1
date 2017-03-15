<?php
/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Changes for magetsync_occasion table
 */
$table = $installer->getTable('magetsync_occasion');
$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
$select = 'SELECT * FROM ' . $table.' WHERE id=1';
$query = $readConnection->fetchAll($select);
if (!empty($query)) {
    $installer->run('TRUNCATE TABLE ' . $installer->getConnection()->quoteIdentifier($table));
}

$values = array(
    array(
        'name' => 'anniversary',
        'display' => 'Anniversary',
    ),
    array(
        'name' => 'baby_shower',
        'display' => 'Baby shower',
    ),
    array(
        'name' => 'bachelor_party',
        'display' => 'Bachelor party',
    ),
    array(
        'name' => 'bachelorette_party',
        'display' => 'Bachelorette party',
    ),
    array(
        'name' => 'back_to_school',
        'display' => 'Back to school',
    ),
    array(
        'name' => 'baptism',
        'display' => 'Baptism',
    ),
    array(
        'name' => 'bar_or_bat_mitzvah',
        'display' => 'Bar & Bat Mitzvah',
    ),
    array(
        'name' => 'birthday',
        'display' => 'Birthday',
    ),
    array(
        'name' => 'bridal_shower',
        'display' => 'Bridal shower',
    ),
    array(
        'name' => 'confirmation',
        'display' => 'Confirmation',
    ),
    array(
        'name' => 'divorce',
        'display' => 'Divorce',
    ),
    array(
        'name' => 'engagement',
        'display' => 'Engagement',
    ),
    array(
        'name' => 'first_communion',
        'display' => 'First Communion',
    ),
    array(
        'name' => 'graduation',
        'display' => 'Graduation',
    ),
    array(
        'name' => 'grief_or_mourning',
        'display' => 'Grief & mourning',
    ),
    array(
        'name' => 'housewarming',
        'display' => 'Housewarming',
    ),
    array(
        'name' => 'moving',
        'display' => 'Moving',
    ),
    array(
        'name' => 'pet_loss',
        'display' => 'Pet loss',
    ),
    array(
        'name' => 'prom',
        'display' => 'Prom',
    ),
    array(
        'name' => 'quinceañera_or_sweet_16',
        'display' => 'Quinceañera & Sweet 16',
    ),
    array(
        'name' => 'retirement',
        'display' => 'Retirement',
    ),
    array(
        'name' => 'wedding',
        'display' => 'Wedding',
    )
);

foreach ($values as $value) {
    $installer->getConnection()->insert($table, $value);
}

/**
 * Changes for magetsync_whenmade table
 */
$changes = array(
    '2010_2016'   => array('name' => '2010_2017','display' => '2010 - 2017')
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