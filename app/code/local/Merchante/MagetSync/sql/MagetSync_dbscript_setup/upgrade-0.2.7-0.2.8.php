<?php
/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$listingOccasionColumn =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'), 'occasion');
if($listingOccasionColumn) {
    $installer->getConnection()->dropColumn($installer->getTable('magetsync/listing'), 'occasion');
}
$listingPropertiesColumn =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'), 'properties');
if (!$listingPropertiesColumn) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'properties',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Properties as JSON',
                'after' => 'when_made'
            )
        );
}
$templateOccasionColumn =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/attributeTemplate'), 'occasion');
if($templateOccasionColumn) {
    $installer->getConnection()->dropColumn($installer->getTable('magetsync/attributeTemplate'), 'occasion');
}
$templatePropertiesColumn =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/attributeTemplate'), 'properties');
if (!$templatePropertiesColumn) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/attributeTemplate'), 'properties',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Properties as JSON',
                'after' => 'when_made'
            )
        );
}
$installer->endSetup();