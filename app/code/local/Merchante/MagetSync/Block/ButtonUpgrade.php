<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for creating button in system configuration
 * Class Merchante_MagetSync_Block_ButtonUpgrade
 */
class Merchante_MagetSync_Block_ButtonUpgrade extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /*
    * Set template
    */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magetsync/buttonUpgrade.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $etsyModel = Mage::getModel('magetsync/etsy');
        $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');
        $url = Merchante_MagetSync_Model_Etsy::$merchApi;
        $message = '';
        if($tokenCustomer) {
            $url = $url . "customerVerification/" . $tokenCustomer;
            $response = $etsyModel->curlConnect($url);
            $response = json_decode($response, true);
            $urlLink = '';
            if ($response['url']) {
                $urlPayment = str_replace('_**_', '_CN_', $response['url']);
                $urlLink = '<a target=\'_blank\' href=\'' . $urlPayment . '\'>' . $response['msg_cx'] . '</a>';
            }
            $message = $response['message'] . ' ' . $urlLink;
        }
        return $message;
    }
}
?>