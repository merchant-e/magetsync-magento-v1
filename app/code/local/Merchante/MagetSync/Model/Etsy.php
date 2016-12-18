<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Etsy
 */
class Merchante_MagetSync_Model_Etsy extends Mage_Core_Model_Abstract
{
    /**
     * @var null
     */
    public $OAuth = null;

    /**
     * @var null
     */
    public $apiInfo = null;

    /**
     * @var string
     */
    public static $merchApi = "https://api.magetsync.net/public/v1/";

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/etsy');
    }

    /**
     * @param $data
     * @param bool $update
     */
    public function saveConfiguration($data, $update = false)
    {
        try {
            $testModel = null;
            $info = array(
                'ConsumerKey'       => (isset($data['ConsumerKey']) ? $data['ConsumerKey'] : ''),
                'ConsumerSecret'    => (isset($data['ConsumerSecret']) ? $data['ConsumerSecret'] : ''),
                'TokenSecret'       => (isset($data['TokenSecret']) ? $data['TokenSecret'] : ''),
                'AccessToken'       => (isset($data['AccessToken']) ? $data['AccessToken'] : ''),
                'AccessTokenSecret' => (isset($data['AccessTokenSecret']) ? $data['AccessTokenSecret'] : '')
            );
            if ($update == true) {
                $info = array(
                    'TokenSecret'       => (isset($data['TokenSecret']) ? $data['TokenSecret'] : ''),
                    'AccessToken'       => (isset($data['AccessToken']) ? $data['AccessToken'] : ''),
                    'AccessTokenSecret' => (isset($data['AccessTokenSecret']) ? $data['AccessTokenSecret'] : '')
                );
                $testModel = Mage::getModel('magetsync/etsy')->load(1);
                $testModel->addData($info);
                $testModel->save();
            } else {
                $testModel = Mage::getModel('magetsync/etsy');
                $testModel->setData($info);
                $testModel->save();
            }

            return;
        } catch (Exception $e) {
            Mage::logException($e);

            return;
        }
    }

    /**
     * @return array|null
     */
    public function getConfiguration()
    {
        $data = null;
        $configuration = Mage::getModel('magetsync/etsy')->load(1);
        if ($configuration != null) {
            $data = $configuration->_data;
        }

        return $data;
    }

    public function verifyDataConfiguration()
    {
        $configuration = $this->getConfiguration();
        if ($configuration) {
            $access_token = $configuration['AccessToken'];
            $access_token_secret = $configuration['AccessTokenSecret'];
            $consumerKey = $configuration['ConsumerKey'];
            $consumerSecret = $configuration['ConsumerSecret'];
            $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');
            if ($access_token && $access_token_secret && $consumerKey && $consumerSecret && $tokenCustomer) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $name
     * @param $service
     * @param $obligatory
     * @param $params
     * @return mixed
     */
    public function selectExecute($name, $service, $obligatory = null, $params = null)
    {
        $modelEtsy = Mage::getModel('magetsync/etsy')->load(1);
        $accessToken = $modelEtsy->getData('AccessToken');
        $accessTokenSecret = $modelEtsy->getData('AccessTokenSecret');
        $consumerKey = $modelEtsy->getData('ConsumerKey');
        $consumerSecret = $modelEtsy->getData('ConsumerSecret');
        $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');


        $tokenParams = array(
            'consumerKey'       => $consumerKey,
            'consumerSecret'    => $consumerSecret,
            'tokenCustomer'     => $tokenCustomer,
            'accessToken'       => $accessToken,
            'accessTokenSecret' => $accessTokenSecret
        );
        $isDraftMode = Mage::getStoreConfig(
            'magetsync_section_draftmode/magetsync_group_draft/magetsync_field_listing_draft_mode'
        );
        if ($isDraftMode &&
            ($service != "uploadListingImage" && $service != "deleteListingImage" && $service != "findAllListingImages")
        ) {
            $params['state'] = 'draft';
        }

        # Our new data
        $data = array(
            'obligatory' => json_encode($obligatory, true),
            'tokens'     => json_encode($tokenParams, true),
            'params'     => json_encode($params, true)
        );

        $url = self::$merchApi . $name . '/' . $service;
        $response = $this->curlConnect($url, $data);
        $log = array(
            "url"      => $url,
            "response" => $response
        );

        $this->log(print_r($log, 1));
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $url
     * @param null $data
     * @param int $option
     * @return mixed
     */
    public function curlConnect($url, $data = null, $option = 1)
    {
        try {

            # Create a connection
            $ch = curl_init($url);

            if ($data != null && $option == 1) {
                # Form data string
                $postString = http_build_query($data, '', '&');
            }

            //curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);

            # Setting our options
            if ($data != null) {
                curl_setopt($ch, CURLOPT_POST, 1);
                if ($option == 1) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
            } else {
                curl_setopt($ch, CURLOPT_POST, 0);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $useOpenSSL = Mage::getStoreConfig('magetsync_section/magetsync_group_settings/magetsync_field_open_ssl');
            if (!$useOpenSSL) {
                if (OPENSSL_VERSION_NUMBER < 0x009080bf) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                } else {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                }
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            //curl_setopt($ch, CURLOPT_SSLVERSION,3);
            curl_setopt($ch, CURLOPT_CAINFO, Mage::getBaseDir() . "/CACerts/cacert.pem");
            # Get the response
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->log("[CURL Error] " . print_r(curl_error($ch), true));
            }

            curl_close($ch);

            return $response;
        } catch (Exception $e) {
            $this->log("[CURL Exception] " . print_r($e, true));
        }
    }

    /**
     * Log into file
     *
     * @param $message
     * @throws Merchante_MagetSync_ApiClientException
     */
    protected function log($message)
    {
        $loggerBaseName = strtolower(get_called_class());

        Mage::helper('magetsync/log')->log($message, $loggerBaseName);
    }

    /**
     * Log exception
     *
     * @param $message
     * @throws Merchante_MagetSync_ApiClientException
     */
    protected function logException($message)
    {
        $loggerBaseName = strtolower(get_called_class());

        Mage::helper('magetsync/log')->logException($message, $loggerBaseName);
    }

}