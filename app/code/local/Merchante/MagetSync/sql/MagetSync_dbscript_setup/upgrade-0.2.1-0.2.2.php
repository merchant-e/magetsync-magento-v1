<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('magetsync_attributetemplate')};
    ");

$table = $installer->getConnection()
    ->newTable($installer->getTable('magetsync/attributeTemplate'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable' => true,
    ), 'category_id')
    ->addColumn('subcategory_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 60, array(
        'nullable' => true,
    ), 'subcategory_id')
    ->addColumn('subsubcategory_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ), 'subsubcategory_id')
    ->addColumn('subcategory4_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ), 'subcategory4_id')
    ->addColumn('subcategory5_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ), 'subcategory5_id')
    ->addColumn('subcategory6_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ), 'subcategory6_id')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => true,
    ), 'title')
    ->addColumn('prepended_template', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => true,
        'default' => null,
        'unsigned' => true,
        'length' => 2
    ), 'prepended_template')
    ->addColumn('appended_template', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => true,
        'default' => null,
        'unsigned' => true,
        'length' => 2
    ), 'appended_template')
    ->addColumn('style_one', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        'nullable' => true,
        'default' => null,
        'unsigned' => true
    ), 'style_one')
    ->addColumn('style_two', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        'nullable' => true,
        'default' => null,
        'unsigned' => true
    ), 'style_two')
    ->addColumn('should_auto_renew', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => true,
        'default' => 0,
        'unsigned' => true
    ), 'should_auto_renew')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
    ), 'updated_at')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable' => true,
    ), 'price')
    ->addColumn('tags', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(
        'nullable' => true,
    ), 'tags')
    ->addColumn('materials', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(
        'nullable' => true,
    ), 'materials')
    ->addColumn('shop_section_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable' => true,
    ), 'shop_section_id')
    ->addColumn('shipping_template_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable' => true,
    ), 'shipping_template_id')
    ->addColumn('who_made', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable' => true,
    ), 'who_made')
    ->addColumn('is_supply', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'nullable' => true,
    ), 'is_supply')
    ->addColumn('when_made', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable' => true,
    ), 'when_made')
    ->addColumn('recipient', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable' => true,
    ), 'recipient')
    ->addColumn('occasion', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable' => true,
    ), 'occasion')
    ->addColumn('product_ids', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => true,
    ), 'product_ids')
    ->addColumn('products_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
        'default' => 0
    ), 'products_count');

$installer->getConnection()->createTable($table);

$installer->endSetup();