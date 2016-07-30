<?php
$installer = $this;
$installer->startSetup();
$tableName = $installer->getTable('magetsync/listing');
$indexName = $installer->getIdxName($tableName, array('listing_id'));
$installer->getConnection()->addIndex($tableName,
    $indexName,
    array('listing_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);
/*->modifyColumn($installer->getTable('magetsync/listing'), 'listing_id', array(
    'default' => null,
));*/

$installer->endSetup();

?>