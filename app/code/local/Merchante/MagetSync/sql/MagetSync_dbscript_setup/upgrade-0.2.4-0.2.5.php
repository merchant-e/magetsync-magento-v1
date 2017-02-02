<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$column =  $installer->getConnection()->tableColumnExists($installer->getTable('magetsync/listing'),'is_custom_price');
if(!$column) {
    $installer->getConnection()
        ->addColumn($installer->getTable('magetsync/listing'), 'is_custom_price',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Should price renew on product save',
                'after' => 'attribute_template_id'
            )
        );
    $installer->endSetup();
}