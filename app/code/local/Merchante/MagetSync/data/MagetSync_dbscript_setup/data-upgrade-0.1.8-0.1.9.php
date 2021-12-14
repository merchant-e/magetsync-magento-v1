<?php
/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 **********DATA UPDATE SCRIPT'S***********
 *****************************************/


$changes = array(
    '2010_2015'   => array('name' => '2010_2016','display' => '2010 - 2016'),
    '1996_1999'   => array('name' => '1997_1999','display' => '1997 - 1999'),
    'before_1996' => array('name' => 'before_1997','display' => 'Before 1997'),
    '1990_1995'   => array('name' => '1990_1996','display' => '1990 - 1996')
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