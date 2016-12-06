<?php

/******************************************
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = $this;
$installer->startSetup();

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/mappingEtsy')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/mappingEtsy'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('etsy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'etsy_id')
        ->addColumn('etsy_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
            'nullable' => false,
        ), 'etsy_name')
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'product_id')
        ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
            'nullable' => true,
        ), 'product_name')
        ->addColumn('product_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'product_sku')
        ->addColumn('state', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'default' => null,
            'nullable' => true,
            'unsigned' => true,
        ), 'state');

    $installer->getConnection()->createTable($table);
}
$installer->endSetup();