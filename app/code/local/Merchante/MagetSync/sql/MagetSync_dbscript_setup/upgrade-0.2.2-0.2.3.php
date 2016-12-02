<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn($installer->getTable('magetsync/listing'),
        'attribute_template_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default' => 0,
            'comment' => 'Attribute Template ID that product is assigned to',
            'after'   => 'sync_ready'
        )
    );
$installer->endSetup();