<?php

/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('magetsync_logdata')};
    ");
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/logData'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'id')
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'entity_id')
        ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'entity_type')
        ->addColumn('level_error', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'level_error')
        ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
        ), 'date')
        ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
            'length' => 500,
            'nullable' => true,
        ), 'message');

    $installer->getConnection()->createTable($table);


$installer->endSetup();