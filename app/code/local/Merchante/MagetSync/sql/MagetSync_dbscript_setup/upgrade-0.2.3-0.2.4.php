<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$connection->addColumn($installer->getTable('magetsync/attributeTemplate'),
        'pricing_rule',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'default' => '',
            'comment' => 'Increase, decrease or original price',
            'after'   => 'price'
        )
    );

$connection->addColumn($installer->getTable('magetsync/attributeTemplate'),
        'affect_value',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'default' => 0,
            'comment' => 'Price delta value',
            'after'   => 'pricing_rule'
        )
    );

$connection->addColumn($installer->getTable('magetsync/attributeTemplate'),
        'affect_strategy',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'default' => '',
            'comment' => 'Fixed or percent',
            'after'   => 'affect_value'
        )
    );

$installer->endSetup();