<?php

/******************************************
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();


    $installer->run("
    DROP TABLE IF EXISTS {$this->getTable('magetsync_style')};
        ");

    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/style'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('style', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => false,
        ), 'name');

    $installer->getConnection()->createTable($table);


$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'style_one');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'style_one', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 30,
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'style_one'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'style_two');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'style_two', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 30,
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'style_two'
        ));
}


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