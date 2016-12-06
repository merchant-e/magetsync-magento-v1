<?php
/******************************************
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 *************DATA SCRIPT'S***************
 *****************************************/

$installer = $this;
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');

$select = 'SELECT * FROM ' . $installer->getTable('magetsync_processingtime').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {

    $values = array(
        array(
            'label' => '1 business day',
            'max' => '1',
            'min' => '1',
            'frequency' => 'business day',
        ),
        array(
            'label' => '1-2 business days',
            'max' => '2',
            'min' => '1',
            'frequency' => 'business days',
        ),
        array(
            'label' => '1-3 business days',
            'max' => '3',
            'min' => '1',
            'frequency' => 'business days',
        ),
        array(
            'label' => '3-5 business days',
            'max' => '5',
            'min' => '3',
            'frequency' => 'business days',
        ),
        array(
            'label' => '1-2 weeks',
            'max' => '2',
            'min' => '1',
            'frequency' => 'weeks',
        ),
        array(
            'label' => '2-3 weeks',
            'max' => '3',
            'min' => '2',
            'frequency' => 'weeks',
        ),
        array(
            'label' => '3-4 weeks',
            'max' => '4',
            'min' => '3',
            'frequency' => 'weeks',
        ),
        array(
            'label' => '4-6 weeks',
            'max' => '6',
            'min' => '4',
            'frequency' => 'weeks',
        ),
        array(
            'label' => '6-8 weeks',
            'max' => '8',
            'min' => '6',
            'frequency' => 'weeks',
        ),
    );

    foreach ($values as $value) {
        $installer->getConnection()->insert($installer->getTable('magetsync_processingtime'), $value);
    }
}

$select = 'SELECT * FROM ' . $installer->getTable('magetsync_category').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {
    $categoryModel = Mage::getModel('magetsync/category');
    $dataApi = $categoryModel->getSellerTaxonomy(null, null);
    if ($dataApi['status'] == true) {
        $values = json_decode(json_decode($dataApi['result']), true);
        $values = (isset($values['results']) ? $values['results'] : null);
        $categoryModel->recursiveCategories($values, $installer);
    } else {
        Mage::log("Error: " . print_r($dataApi['message'], true), null, 'magetsync_dataInstall.log');
    }
}

$select = 'SELECT * FROM ' . $installer->getTable('magetsync_whenmade').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {

    $values = array(
        array(
            'name' => 'made_to_order',
            'display' => 'made to order',
        ),
        array(
            'name' => '2010_2015',
            'display' => '2010 - 2015',
        ),
        array(
            'name' => '2000_2009',
            'display' => '2000s',
        ),
        array(
            'name' => '1996_1999',
            'display' => '1996 - 1999',
        ),
        array(
            'name' => 'before_1996',
            'display' => 'before 1996',
        ),
        array(
            'name' => '1990_1995',
            'display' => '1990 - 1995',
        ),
        array(
            'name' => '1980s',
            'display' => '1980s',
        ),
        array(
            'name' => '1970s',
            'display' => '1970s',
        ),
        array(
            'name' => '1960s',
            'display' => '1960s',
        ),
        array(
            'name' => '1950s',
            'display' => '1950s',
        ),
        array(
            'name' => '1940s',
            'display' => '1940s',
        ),
        array(
            'name' => '1930s',
            'display' => '1930s',
        ),
        array(
            'name' => '1920s',
            'display' => '1920s',
        ),
        array(
            'name' => '1910s',
            'display' => '1910s',
        ),
        array(
            'name' => '1900s',
            'display' => '1900s',
        ),
        array(
            'name' => '1800s',
            'display' => '1800s',
        ),
        array(
            'name' => '1700s',
            'display' => '1700s',
        ),
        array(
            'name' => 'before_1700',
            'display' => 'before 1700',
        ),
    );

    foreach ($values as $value) {
        $installer->getConnection()->insert($installer->getTable('magetsync_whenmade'), $value);
    }
}
