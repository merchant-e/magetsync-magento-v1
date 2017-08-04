<?php

/******************************************
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 ************UPGRADE SCRIPT'S*************
 *****************************************/

$installer = Mage::getModel('eav/entity_setup', 'core_setup');
$installer->startSetup();
/********************Update Attribute***************************/
$installer->updateAttribute('catalog_product', 'synchronizedEtsy', 'is_configurable', 0);
/***************************************************************/
$installer->endSetup();