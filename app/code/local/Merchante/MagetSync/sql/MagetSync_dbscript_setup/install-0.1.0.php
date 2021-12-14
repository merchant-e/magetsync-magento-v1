<?php

/******************************************
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 ************INSTALL SCRIPT'S*************
 *****************************************/

$installer = $this;

$installer->startSetup();

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/listing')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/listing'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('listing_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'listing_id')
        ->addColumn('state', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'state')
        ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'user_id')
        ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable' => true,
        ), 'category_id')
        ->addColumn('subcategory_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 60, array(
            'nullable' => true,
        ), 'subcategory_id')
        ->addColumn('subsubcategory_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'subsubcategory_id')
        ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 60, array(
            'nullable' => true,
        ), 'title')
        ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
            'nullable' => true,
        ), 'description')
        ->addColumn('creation_tsz', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'creation_tsz')
        ->addColumn('ending_tsz', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'ending_tsz')
        ->addColumn('original_creation_tsz', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'original_creation_tsz')
        ->addColumn('last_modified_tsz', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'last_modified_tsz')
        ->addColumn('price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable' => true,
        ), 'price')
        ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => true,
        ), 'currency_code')
        ->addColumn('quantity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'quantity')
        ->addColumn('tags', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(
            'nullable' => true,
        ), 'tags')
        ->addColumn('category_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(
            'nullable' => true,
        ), 'category_path')
        ->addColumn('category_path_ids', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(
            'nullable' => true,
        ), 'category_path_ids')
        ->addColumn('materials', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70, array(
            'nullable' => true,
        ), 'materials')
        ->addColumn('shop_section_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => true,
        ), 'shop_section_id')
        ->addColumn('featured_rank', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'featured_rank')
        ->addColumn('state_tsz', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'state_tsz')
        ->addColumn('url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 250, array(
            'nullable' => true,
        ), 'url')
        ->addColumn('views', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'views')
        ->addColumn('num_favorers', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'num_favorers')
        ->addColumn('shipping_template_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => true,
        ), 'shipping_template_id')
        ->addColumn('shipping_profile_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => true,
        ), 'shipping_profile_id')
        ->addColumn('processing_min', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'processing_min')
        ->addColumn('processing_max', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'processing_max')
        ->addColumn('who_made', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'who_made')
        ->addColumn('is_supply', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'is_supply')
        ->addColumn('when_made', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'when_made')
        ->addColumn('is_private', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'is_private')
        ->addColumn('occasion', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'occasion')
        ->addColumn('non_taxable', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'non_taxable')
        ->addColumn('is_customizable', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'is_customizable')
        ->addColumn('is_digital', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'is_digital')
        ->addColumn('state', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
            'nullable' => true,
        ), 'state')
        ->addColumn('file_data', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable' => true,
        ), 'file_data')
        ->addColumn('has_variations', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'has_variations')
        ->addColumn('language', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
            'nullable' => true,
        ), 'language')
        ->addColumn('idproduct', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'idproduct')
        ->addColumn('sync', Varien_Db_Ddl_Table::TYPE_CHAR, 1, array(
            'nullable' => true,
        ), 'sync')
        ->addColumn('variations', Varien_Db_Ddl_Table::TYPE_VARCHAR, 2000, array(
            'nullable' => true,
        ), 'variations');

    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/etsy')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/etsy'))
        ->addColumn('IdConfiguration', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'IdConfiguration')
        ->addColumn('ConsumerKey', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => true,
        ), 'ConsumerKey')
        ->addColumn('ConsumerSecret', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => true,
        ), 'ConsumerSecret')
        ->addColumn('TokenSecret', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => true,
        ), 'TokenSecret')
        ->addColumn('AccessToken', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => true,
        ), 'AccessToken')
        ->addColumn('AccessTokenSecret', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => true,
        ), 'AccessTokenSecret');
    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/orderEtsy')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/orderEtsy'))
        ->addColumn('order_etsy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'order_etsy_id')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'order_id')
        ->addColumn('receipt_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'receipt_id')
        ->addColumn('is_order_etsy', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable' => true,
        ), 'is_order_etsy');
    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/shippingTemplate')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/shippingTemplate'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('shipping_template_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => false,
        ), 'shipping_template_id')
        ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable' => true,
        ), 'title')
        ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'user_id')
        ->addColumn('processing', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'processing')
        ->addColumn('origin_country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'origin_country_id');
    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/shopSection')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/shopSection'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('shop_section_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'shop_section_id')
        ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable' => true,
        ), 'title')
        ->addColumn('rank', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'rank')
        ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'user_id')
        ->addColumn('active_listing_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'active_listing_count');
    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/shippingEntry')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/shippingEntry'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('shipping_template_entry_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => false,
        ), 'shipping_template_entry_id')
        ->addColumn('shipping_template_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
            'nullable' => false,
        ), 'shipping_template_id')
        ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'currency_code')
        ->addColumn('origin_country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'origin_country_id')
        ->addColumn('destination_country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'destination_country_id')
        ->addColumn('destination_region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'destination_region_id')
        ->addColumn('primary_cost', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'primary_cost')
        ->addColumn('secondary_cost', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'secondary_cost');

    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/countryEtsy')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/countryEtsy'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
        ), 'country_id')
        ->addColumn('iso_country_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 2, array(
            'nullable' => false,
        ), 'iso_country_code')
        ->addColumn('world_bank_country_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
            'nullable' => true,
        ), 'world_bank_country_code')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'name')
        ->addColumn('slug', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'slug')
        ->addColumn('lat', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'lat')
        ->addColumn('lon', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable' => true,
        ), 'lon');

    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/whoMade')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/whoMade'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'name')
        ->addColumn('display', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'display');

    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/occasion')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/occasion'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'name')
        ->addColumn('display', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'display');

    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/variation')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/variation'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('propertyid', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'propertyid')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable' => true,
        ), 'name');

    $installer->getConnection()->createTable($table);
}

if ($installer->getConnection()->isTableExists($installer->getTable('magetsync/imageEtsy')) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('magetsync/imageEtsy'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'id')
        ->addColumn('listing_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'listing_id')
        ->addColumn('listing_image_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'listing_image_id')
        ->addColumn('file', Varien_Db_Ddl_Table::TYPE_VARCHAR, 200, array(
            'nullable' => true,
        ), 'file');

    $installer->getConnection()->createTable($table);
}

$installer->endSetup();

$installer = Mage::getModel('eav/entity_setup', 'core_setup');

$installer->startSetup();

/******************ATTRIBUTTES*******************************/
if (!$installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'synchronizedEtsy')) {
    $installer->addAttribute('catalog_product', 'synchronizedEtsy', array(
        'type' => 'varchar',
        'input' => 'select',
        'group' => 'General',
        'input_renderer' => 'magetsync/select_render',
        'source' => 'eav/entity_attribute_source_boolean',
        'label' => 'Synchronize with Etsy',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'required' => false,
        'visible' => true,
        'default' => 0,
        //'is_configurable' => 0,
        'user_defined' => 1,
    ));
}
$installer->endSetup();
/************************************************************/