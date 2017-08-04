<?php
/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/mappingEtsy'),'etsy_name');
if($column) {
    $installer->getConnection()
        ->modifyColumn($installer->getTable('magetsync/mappingEtsy'), 'etsy_name', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 140,
            'nullable' => false,
            'comment' => 'etsy_name'
        ));
}
$installer->endSetup();