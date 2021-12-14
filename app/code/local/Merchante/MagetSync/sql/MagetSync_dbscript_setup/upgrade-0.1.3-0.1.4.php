<?php

/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'prepended_template');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'prepended_template', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 2,
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'prepended_template'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'appended_template');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'appended_template', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 2,
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'appended_template'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'should_auto_renew');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'should_auto_renew', array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'default' => 0,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'should_auto_renew'
        ));
}

$installer->endSetup();