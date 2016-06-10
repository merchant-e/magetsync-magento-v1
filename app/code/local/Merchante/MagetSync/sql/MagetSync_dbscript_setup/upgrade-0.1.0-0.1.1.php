<?php

/******************************************
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('magetsync_category')};
    ");

$table = $installer->getConnection()
    ->newTable($installer->getTable('magetsync/category'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'category_id')
    ->addColumn('category_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable'  => true,
    ), 'category_name')
    ->addColumn('short_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => true,
    ), 'short_name')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'parent_id')
    ->addColumn('level_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'level_id')
    ->addColumn('level', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'level');

$installer->getConnection()->createTable($table);

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('magetsync_whenmade')};
    ");

$table = $installer->getConnection()
    ->newTable($installer->getTable('magetsync/whenMade'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable'  => true,
    ), 'name')
    ->addColumn('display', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable'  => true,
    ), 'display');

$installer->getConnection()->createTable($table);

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('magetsync_processingtime')};
    ");

$table = $installer->getConnection()
    ->newTable($installer->getTable('magetsync/processingTime'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable'  => true,
    ), 'label')
    ->addColumn('max', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'max')
    ->addColumn('min', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'min')
    ->addColumn('frequency', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable'  => true,
    ), 'frequency');

$installer->getConnection()->createTable($table);

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'subcategory4_id');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'subcategory4_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'default' => null,
            'nullable' => true,
            'comment' => 'subcategory4_id'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'subcategory5_id');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'subcategory5_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'default' => null,
            'nullable' => true,
            'comment' => 'subcategory5_id'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'subcategory6_id');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'subcategory6_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'default' => null,
            'nullable' => true,
            'comment' => 'subcategory6_id'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'subcategory7_id');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'subcategory7_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'default' => null,
            'nullable' => true,
            'comment' => 'subcategory7_id'
        ));
}

$installer->endSetup();