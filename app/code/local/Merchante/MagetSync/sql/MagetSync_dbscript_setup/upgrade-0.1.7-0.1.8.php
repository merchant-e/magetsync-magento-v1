<?php

/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/mappingEtsy'),'thumbnail');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/mappingEtsy'), 'thumbnail', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 150,
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'thumbnail'
        ));
}
$installer->endSetup();