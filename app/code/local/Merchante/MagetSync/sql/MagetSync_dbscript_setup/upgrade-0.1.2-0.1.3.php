<?php

/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'title');
if($column) {
    $installer->getConnection()
        ->modifyColumn($installer->getTable('magetsync/listing'), 'title', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 140,
            'default' => null,
            'nullable' => false,
            'comment' => 'title'
        ));
}

$installer->endSetup();