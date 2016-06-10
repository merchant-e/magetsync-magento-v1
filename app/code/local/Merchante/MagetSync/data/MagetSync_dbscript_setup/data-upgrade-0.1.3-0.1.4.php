<?php
/******************************************
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 *************DATA SCRIPT'S***************
 *****************************************/

$installer = $this;
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');


$select = 'SELECT * FROM ' . $installer->getTable('magetsync_style').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {
    $styleModel = Mage::getModel('magetsync/style');
    $dataApi = $styleModel->findSuggestedStyles(null, null);

    if ($dataApi['status'] == true) {
        $values = json_decode(json_decode($dataApi['result']), true);
        $values = (isset($values['results']) ? $values['results'] : null);

        foreach ($values as $value) {
            $data['style'] = $value['style'];
            $installer->getConnection()->insert($installer->getTable('magetsync_style'), $data);
        }

    } else {
        Mage::log("Error: " . print_r($dataApi['message'], true), null, 'styleInstall.log');
    }


}




