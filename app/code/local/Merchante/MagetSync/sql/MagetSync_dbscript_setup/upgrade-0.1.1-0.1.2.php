<?php

/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'quantity_has_changed');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'quantity_has_changed', array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'quantity_has_changed'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'enabled');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'enabled', array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'default' => 1,
            'nullable' => false,
            'unsigned' => true,
            'comment' => 'enabled'
        ));
}

$installer->endSetup();