<?php

/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'tags');
if($column) {
    $installer->getConnection()
        ->modifyColumn($installer->getTable('magetsync/listing'), 'tags', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 250,
            'default' => null,
            'nullable' => true,
            'comment' => 'tags'
        ));
}

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'materials');
if($column) {
    $installer->getConnection()
        ->modifyColumn($installer->getTable('magetsync/listing'), 'materials', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 250,
            'default' => null,
            'nullable' => true,
            'comment' => 'materials'
        ));
}


$installer->endSetup();