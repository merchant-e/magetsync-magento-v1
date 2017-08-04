<?php
/******************************************
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 *************DATA SCRIPT'S***************
 *****************************************/

$installer = $this;
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');


$select = 'SELECT * FROM ' . $installer->getTable('magetsync_configuration').' WHERE IdConfiguration=1';
$query = $readConnection->fetchAll($select);
if(!$query) {
    $values = array(
        array(
            'ConsumerKey' => 'teqhxih203h7wdzgw7yuonup',
            'ConsumerSecret' => 'dijq4mjoyd',
        ),
    );
    foreach ($values as $value) {
        $installer->getConnection()->insert($installer->getTable('magetsync_configuration'), $value);
    }
}

$select = 'SELECT * FROM ' . $installer->getTable('magetsync_whomade').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {

    $values = array(
        array(
            'name' => 'i_did',
            'display' => 'I did',
        ),
        array(
            'name' => 'collective',
            'display' => 'A member of my shop',
        ),
        array(
            'name' => 'someone_else',
            'display' => 'Another company or person',
        ),
    );

    foreach ($values as $value) {
        $installer->getConnection()->insert($installer->getTable('magetsync_whomade'), $value);
    }
}

$select = 'SELECT * FROM ' . $installer->getTable('magetsync_occasion').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {

    $values = array(
        array(
            'name' => 'anniversary',
            'display' => 'Anniversary',
        ),
        array(
            'name' => 'baptism',
            'display' => 'Baptism',
        ),
        array(
            'name' => 'bar_or_bat_mitzvah',
            'display' => 'Bar or bat mitzvah',
        ),
        array(
            'name' => 'birthday',
            'display' => 'Birthday',
        ),
        array(
            'name' => 'canada_day',
            'display' => 'Canada day',
        ),
        array(
            'name' => 'chinese_new_year',
            'display' => 'Chinese new year',
        ),
        array(
            'name' => 'cinco_de_mayo',
            'display' => 'Cinco de mayo',
        ),
        array(
            'name' => 'confirmation',
            'display' => 'Confirmation',
        ),
        array(
            'name' => 'christmas',
            'display' => 'Christmas',
        ),
        array(
            'name' => 'day_of_the_dead',
            'display' => 'Day of the dead',
        ),
        array(
            'name' => 'easter',
            'display' => 'Easter',
        ),
        array(
            'name' => 'eid',
            'display' => 'Eid',
        ),
        array(
            'name' => 'engagement',
            'display' => 'Engagement',
        ),
        array(
            'name' => 'fathers_day',
            'display' => 'Fathers day',
        ),
        array(
            'name' => 'get_well',
            'display' => 'Get well',
        ),
        array(
            'name' => 'graduation',
            'display' => 'Graduation',
        ),
        array(
            'name' => 'halloween',
            'display' => 'Halloween',
        ),
        array(
            'name' => 'hanukkah',
            'display' => 'Hanukkah',
        ),
        array(
            'name' => 'housewarming',
            'display' => 'Housewarming',
        ),
        array(
            'name' => 'kwanzaa',
            'display' => 'Kwanzaa',
        ),
        array(
            'name' => 'prom',
            'display' => 'Prom',
        ),
        array(
            'name' => 'july_4th',
            'display' => 'July 4th',
        ),
        array(
            'name' => 'mothers_day',
            'display' => 'Mothers day',
        ),
        array(
            'name' => 'new_baby',
            'display' => 'New baby',
        ),
        array(
            'name' => 'new_years',
            'display' => 'New years',
        ),
        array(
            'name' => 'quinceanera',
            'display' => 'Quinceanera',
        ),
        array(
            'name' => 'retirement',
            'display' => 'Retirement',
        ),
        array(
            'name' => 'st_patricks_day',
            'display' => 'St Patricks day',
        ),
        array(
            'name' => 'sweet_16',
            'display' => 'Sweet 16',
        ),
        array(
            'name' => 'sympathy',
            'display' => 'Sympathy',
        ),
        array(
            'name' => 'thanksgiving',
            'display' => 'Thanksgiving',
        ),
        array(
            'name' => 'valentines',
            'display' => 'Valentines',
        ),
        array(
            'name' => 'wedding',
            'display' => 'Wedding',
        ),
    );

    foreach ($values as $value) {
        $installer->getConnection()->insert($installer->getTable('magetsync_occasion'), $value);
    }
}

$select = 'SELECT * FROM ' . $installer->getTable('magetsync_variation').' WHERE id=1';
$query = $readConnection->fetchAll($select);
if(!$query) {

    $values = array(
        array(
            'name' => 'Color',
            'propertyid' => 200,
        ),
        array(
            'name' => 'Custom 1',
            'propertyid' => 513,
        ),
        array(
            'name' => 'Custom 2',
            'propertyid' => 514,
        ),
        array(
            'name' => 'Device',
            'propertyid' => 515,
        ),
        array(
            'name' => 'Diameter',
            'propertyid' => 504,
        ),
        array(
            'name' => 'Dimensions',
            'propertyid' => 501,
        ),
        array(
            'name' => 'Fabric',
            'propertyid' => 502,
        ),
        array(
            'name' => 'Finish',
            'propertyid' => 500
        ),
        array(
            'name' => 'Flavor',
            'propertyid' => 503,
        ),
        array(
            'name' => 'Height',
            'propertyid' => 505,
        ),
        array(
            'name' => 'Length',
            'propertyid' => 506,
        ),
        array(
            'name' => 'Material',
            'propertyid' => 507,
        ),
        array(
            'name' => 'Pattern',
            'propertyid' => 508
        ),
        array(
            'name' => 'Scent',
            'propertyid' => 509,
        ),
        array(
            'name' => 'Style',
            'propertyid' => 510,
        ),
        array(
            'name' => 'Weight',
            'propertyid' => 511,
        ),
        array(
            'name' => 'Size',
            'propertyid' => 100,
        ),
        array(
            'name' => 'Width',
            'propertyid' => 512,
        ),
        array(
            'name' => 'Diameter Scale',
            'propertyid' => 302,
        ),
        array(
            'name' => 'Dimensions Scale',
            'propertyid' => 303,
        ),
        array(
            'name' => 'Height Scale',
            'propertyid' => 304,
        ),
        array(
            'name' => 'Length Scale',
            'propertyid' => 305,
        ),
        array(
            'name' => 'Recipient',
            'propertyid' => 266817057,
        ),
        array(
            'name' => 'Sizing Scale',
            'propertyid' => 300,
        ),
        array(
            'name' => 'Weight Scale',
            'propertyid' => 301,
        ),
        array(
            'name' => 'Width Scale',
            'propertyid' => 306,
        ),
    );

    foreach ($values as $value) {
        $installer->getConnection()->insert($installer->getTable('magetsync_variation'), $value);
    }
}
