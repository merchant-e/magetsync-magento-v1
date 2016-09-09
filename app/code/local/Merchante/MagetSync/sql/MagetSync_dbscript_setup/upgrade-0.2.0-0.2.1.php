<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn($installer->getTable('magetsync/listing'),
        'sync_ready',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'nullable' => true,
            'default' => 0,
            'comment' => 'All required attributes setup',
            'after'   => 'should_auto_renew'
        )
    );
$installer->endSetup();