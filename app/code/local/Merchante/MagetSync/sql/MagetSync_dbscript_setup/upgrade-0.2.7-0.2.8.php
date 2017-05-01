<?php
/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$occasionColumn =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),' occasion');
if($occasionColumn) {
    $installer->getConnection()->dropColumn($installer->getTable('magetsync/listing'), 'occasion');
}
$propertiesColumn =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'), 'properties');
if (!$propertiesColumn) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'properties',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'comment' => 'Properties as JSON',
                'after' => 'recipient'
            )
        );
}
$installer->endSetup();