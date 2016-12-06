<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * General class in root controller
 * Class Merchante_Magetsync_IndexController
 */
class Merchante_MagetSync_Adminhtml_Magetsync_ApiController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Method for render layout
     */
    public function indexAction()
	{
    	$this->loadLayout();
    	$this->renderLayout();
    }

    /**
     * Method for authorizing connection with Etsy
     */
    public  function authorizeAction()
    {
        try
        {
            $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');
            $shop_name = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
            $language = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_language');
            if($language <> null) {
                if ($tokenCustomer != '') {
                    if($shop_name != '') {
                        //$key = $this->getRequest()->getParam('key');
                        $bverifier = '';
                        $btoken = '';
                        $btokenSecret = '';
                        $access_token = '';
                        $access_token_secret = '';
                        $etsyModel = Mage::getModel('magetsync/etsy');
                        $configuration = $etsyModel->getConfiguration();
                        /*************************/

                        if ($configuration != null && count($configuration) > 0) {
                            $btokenSecret = $configuration['TokenSecret'];
                            $access_token = $configuration['AccessToken'];
                            $access_token_secret = $configuration['AccessTokenSecret'];
                        }

                        /**Vars received for GET method
                         * for the verification process**/
                        if (isset($_GET['oauth_verifier'])) {
                            $bverifier = $_GET['oauth_verifier'];
                        }
                        if (isset($_GET['oauth_token'])) {
                            $btoken = $_GET['oauth_token'];
                        }
                        /*****************************************/

                        $url = Merchante_MagetSync_Model_Etsy::$merchApi;
                        $url = $url . "verificationAuthEtsy/" . $tokenCustomer;
                        $resultVerification = $etsyModel->curlConnect($url);
                        $rVerification = json_decode($resultVerification,true);


                        if($rVerification['success']) {
                            /******************************************
                             * The process for the authorization against
                             * the Etsy api has three steps .
                             ******************************************/

                            /**Step 1: Generate an url that lets us authenticate
                             * and authorize against ETSY using the OAuth protocol**/

                            if ($shop_name == $rVerification['shop_name'] && $rVerification['authorized'] == true) {
                                Mage::getSingleton('adminhtml/session')
                                    ->addSuccess(Mage::helper('magetsync')->__('This shop has been already authorized'));
                                Mage::getSingleton('adminhtml/session')
                                    ->settestData(false);
                                $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                                return;
                            }

                            if ($rVerification['success'] == true && ($rVerification['shop_name'] == '' || $shop_name != $rVerification['shop_name'])) {

                                //If verifyKey is empty that means: we are in the first step
                                if ($bverifier == '') {
                                    /*if( $_SERVER['HTTPS'] || strtolower($_SERVER['HTTPS']) == 'on' )
                                    {
                                        $baseUrl = Mage::getUrl('',array('_secure'=>true));
                                    }else{
                                        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                                    }*/
                                    //Mage::helper('core/url')->getCurrentUrl();
                                    $baseUrl = Mage::helper("adminhtml")->getUrl('adminhtml/magetsync_api/authorize');
                                    $obligatory = array('callBackUrl' => $baseUrl);
                                    $response = $etsyModel->selectExecute('OAuth', 'getLoginUrl', $obligatory);
                                    if ($response['status'] != false) {
                                        $data = array('TokenSecret' => $response['secret']);
                                        $url = $response['url'];
                                        $etsyModel->saveConfiguration($data, true);
                                        echo "<script type=\"text/javascript\">";
                                        echo "window.location ='$url';";
                                        echo "</script>";
                                        exit();
                                    } else {
                                        Mage::getSingleton('adminhtml/session')
                                            ->addError($response['message']);
                                        $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                                        return;
                                    }

                                } else {
                                    // /**Step 2: We need to request to ETSY that
                                    // allows us access to some grants in the platform**/

                                    $obligatory = array('btoken' => $btoken, 'btokenSecret' => $btokenSecret, 'bverifier' => $bverifier);

                                    $result = $etsyModel->selectExecute('OAuth', 'setVerificationToken', $obligatory);
                                    if ($result['status'] != false) {
                                        $acc_token = $result['result'];
                                        $data = array('TokenSecret' => $btokenSecret, 'AccessToken' => $acc_token['oauth_token'],
                                            'AccessTokenSecret' => $acc_token['oauth_token_secret']);
                                        $etsyModel->saveConfiguration($data, true);
                                    } else {
                                        Mage::getSingleton('adminhtml/session')
                                            ->addError($result['message']);
                                        $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                                        return;
                                    }
                                }

                                /**************************************/
                                /*******Step 3: INITIAL DATA***********/
                                /**************************************/

                                $countryModel = Mage::getModel('magetsync/countryEtsy');
                                $data = $countryModel->setAllCountries();

                                /**************************************/
                                $url = Merchante_MagetSync_Model_Etsy::$merchApi;
                                $url = $url . "customerDate/" . $tokenCustomer . '/' . $shop_name;
                                $etsyModel->curlConnect($url);

                                Mage::log("Path: " . print_r(getcwd() . "/CACerts/cacert.pem", true), null, 'magetsync_certpath.log');

                            }

                            Mage::getSingleton('adminhtml/session')
                                ->addSuccess(Mage::helper('magetsync')->__('Successfully authorised'));
                            Mage::getSingleton('adminhtml/session')
                                ->settestData(false);
                            $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                            return;
                        }else
                        {
                            if(strlen($rVerification['error']) > 0) {
                                Mage::getSingleton('adminhtml/session')
                                    ->addError($rVerification['error']);
                            }else{
                                Mage::getSingleton('adminhtml/session')
                                    ->addError(Mage::helper('magetsync')->__("There was an error verifying your data"));
                            }
                            $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                            return;
                        }
                    }else{
                        Mage::getSingleton('adminhtml/session')
                            ->addError(Mage::helper('magetsync')->__("You must fill your shop name"));
                        $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                        return;
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')
                        ->addError(Mage::helper('magetsync')->__("You must fill in your Secret Token under System > Configuration > MagetSync"));
                    $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                    return;
                }
            }else{
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('magetsync')->__("Must configure Etsy\'s language"));
                $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
                return;
            }
        }catch (Exception $e){
            Mage::log("Error: ".print_r($response, true),null,'magetsync_authorize.log');
            Mage::getSingleton('adminhtml/session')
            ->addError($e->getMessage());
            $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
            return;
        }
    }

    /**
     * Method for deauthorize Etsy access
     */
    public  function deauthorizeAction()
    {
        try
        {
            $etsyModel = Mage::getModel('magetsync/etsy');
            /**************************************/
            $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');
            $url = Merchante_MagetSync_Model_Etsy::$merchApi;
            $url = $url . "resetAuthorizationData/" . $tokenCustomer;
            $result = $etsyModel->curlConnect($url);
            /*************************************/
            if($result['success'])
            {
                $etsyData = $etsyModel->load(1);
                $data = array('TokenSecret'=> '','AccessToken'=>'',
                    'AccessTokenSecret'=>'');
                $etsyData->addData($data);
                $etsyData->save();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('magetsync')->__('Successfully deauthorize'));
            }else{
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($result['error']);
            }
            Mage::getSingleton('adminhtml/session')
                ->settestData(false);
            $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
            return;
        }catch (Exception $e){
            Mage::getSingleton('adminhtml/session')
            ->addError($e->getMessage());
            $this->_redirect('adminhtml/system_config/edit/section/magetsync_section/');
            return;
        }
    }

}
