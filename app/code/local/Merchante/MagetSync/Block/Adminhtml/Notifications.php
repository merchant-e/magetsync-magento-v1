<?php
/**
 * Created by PhpStorm.
 * User: jddm11
 * Date: 9/04/2015
 * Time: 9:32 PM
 */

class Merchante_MagetSync_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getMessage()
    {
        $etsyModel = Mage::getModel('magetsync/etsy');
        $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');
        $url = Merchante_MagetSync_Model_Etsy::$merchApi;
        $message = '';
        if($tokenCustomer) {
            $url = $url . "customerVerification/" . $tokenCustomer;
            $response = $etsyModel->curlConnect($url);
            $response = json_decode($response, true);
            if(array_key_exists('type',$response) !== false && $response['type'] == 2)
            {
                return;
            }
            $urlLink = '';
            $configURL = '<strong>MagetSync</strong>';
            if ($response['url']) {
                $urlPayment = str_replace('_**_', '_NT_', $response['url']);
                $urlLink = '<a target=\'_blank\' href=\'' . $urlPayment . '\'>' . $response['msg_cx'] . '</a>';
            }
            if ($response['show'] == 1) {
                $message = $configURL . ': ' . $response['message'] . ' ' . $urlLink;
                if ($response['days_left']) {
                    if ($response['days_left'] == 1) {
                        $resource = Mage::getSingleton('core/resource');
                        $readConnection = $resource->getConnection('core_read');
                        $table = Mage::getSingleton('core/resource')->getTableName('adminnotification/inbox');

                        $select = 'SELECT * FROM ' . $table . ' WHERE title =\'' . Mage::helper('magetsync')->__('MagetSync Licensing Service: You have 1 day left in your trial/subscription') . '\' AND description =\'' . Mage::helper('magetsync')->__('You have 1 day left in your trial/subscription') . '\'' . ' AND is_remove =\'1\'';
                        $query = $readConnection->fetchAll($select);
                        if ($query) {
                            $write = $resource->getConnection('core_write');
                            $write->update(
                                $table,
                                array("is_remove" => 0),
                                "notification_id=" . $query[0]['notification_id']
                            );
                        } else {
                            $feedData = array();
                            $feedData[] = array(
                                'severity' => 4,
                                'date_added' => gmdate('Y-m-d H:i:s', time()),
                                'title' => Mage::helper('magetsync')->__('MagetSync Licensing Service: You have 1 day left in your trial/subscription'),
                                'description' => Mage::helper('magetsync')->__('You have 1 day left in your trial/subscription'),
                                'url' => Mage::helper('magetsync')->__('http://www.magetsync.com')
                            );

                            Mage::getModel('adminnotification/inbox')->parse($feedData);
                        }

                    }
                }
            }
        }
        return $message;
    }
}