<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for shop section form
 * Class Merchante_Magetsync_Adminhtml_ShopSectionController
 */
class Merchante_MagetSync_Adminhtml_Magetsync_ShopSectionController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  Method for initAction
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('magetsync/shopSection')
            ->_addBreadcrumb('MagetSync Manager','MagetSync Manager');
        return $this;
    }

    /**
     * Method for renderLayout
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Method for edit Shop Section
     */
    public function editAction()
    {
        $testId = $this->getRequest()->getParam('id');
        $testModel = Mage::getModel('magetsync/shopSection')->load($testId);
        if ($testModel->getId() || $testId == 0)
        {
            Mage::register('magetsync_shopsection', $testModel);
            $this->loadLayout();
            $this->_setActiveMenu('magetsync/shopSection');
            $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()
                ->createBlock('magetsync/adminhtml_global_shopSection_edit'))
                ->_addLeft($this->getLayout()
                        ->createBlock('magetsync/adminhtml_global_shopSection_edit_tabs')
                );
            $this->renderLayout();
        }
        else
        {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('magetsync')->__('Shop section does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function syncAction()
    {
        try {
            $shopSectionModel = Mage::getModel('magetsync/shopSection');
            $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
            $obligatory = array('shop_id' => $shop);
            $dataApi = $shopSectionModel->findAllShopSections($obligatory, null);
            if ($dataApi['status'] == true) {
                $results = json_decode(json_decode($dataApi['result']), true);
                $results = $results['results'];
                foreach ($results as $value) {
                    $shopCollection = Mage::getModel('magetsync/shopSection')->getCollection();
                    $queryShop = $shopCollection->getSelect()->where('shop_section_id = ?', $value['shop_section_id']);
                    $queryShop = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryShop);
                    if (!$queryShop) {
                        $shopSectionModel
                            ->addData($value)
                            ->setId($queryShop[0]['id'])
                            ->save();
                    }
                }

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('magetsync')->__('Successfully synchronized'));

                Mage::getSingleton('adminhtml/session')
                    ->settestData(false);
                $this->_redirect('*/*/');
                return;

            }else{
                Mage::getSingleton('adminhtml/session')
                    ->addError($dataApi['message']);
                Mage::getSingleton('adminhtml/session')
                    ->settestData(false);
                $this->_redirect('*/*/');
                return;
            }
        }  catch (Exception $e){
            Mage::log("Error: ".print_r($e, true),null,'magetsync_shopsection.log');
            if($e instanceof OAuthException)
            {
            Mage::getSingleton('adminhtml/session')
            ->addError($e->lastResponse);
            }
            else
            {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            }
            Mage::getSingleton('adminhtml/session')
                ->settestData($this->getRequest()
                        ->getPost()
                );
            return;
            }

    }

    /**
     * Method for redirect new Action to edit Action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Method for save Shop Section
     */
    public function saveAction()
    {
        if(!$this->verifyEtsyApi()){ return; }

        if ($this->getRequest()->getPost())
        {
            try {
                $shopSectionModel = Mage::getModel('magetsync/shopSection');
                $params = array();
                if($this->getRequest()->getParam('user_id'))
                {
                   $params['user_id'] = $this->getRequest()->getParam('user_id');
                }
                if($this->getRequest()->getParam('title'))
                {
                    $params['title'] = $this->getRequest()->getParam('title');
                }

                $language = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_language');
                if($language <> null) { $params['language']= $language;}
                else{ throw new Exception(Mage::helper('magetsync')->__('etsy_language_error')); }
                $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
                $obligatory = array('shop_id'=>$shop);
                $postData = $this->getRequest()->getPost();
                $result = null;
                if( $this->getRequest()->getParam('id') <= 0 )
                {
                    $resultApi = $shopSectionModel->createShopSection($obligatory,$params);
                }
                else {
                    $obligatory['shop_section_id'] = $this->getRequest()->getParam('shop_section_id');
                    $resultApi = $shopSectionModel->updateShopSection($obligatory, $params);
                }
                if($resultApi['status'] == true) {

                    $result = json_decode(json_decode($resultApi['result']),true);
                    $result = $result['results'][0];

                    $postData['shop_section_id'] = $result['shop_section_id'];
                    $postData['user_id'] = $result['user_id'];
                    $postData['rank'] = $result['rank'];
                    $postData['active_listing_count'] = $result['active_listing_count'];

                    $shopSectionModel
                        ->addData($postData)
                        ->setId($this->getRequest()->getParam('id'))
                        ->save();

                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('magetsync')->__('Successfully saved'));
                }
                else
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($resultApi['message']);
                }
                Mage::getSingleton('adminhtml/session')
                    ->settestData(false);
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e){
                Mage::log("Error: ".print_r($e, true),null,'magetsync_shopsection.log');
                if($e instanceof OAuthException)
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($e->lastResponse);
                }
                else
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($e->getMessage());
                }
                Mage::getSingleton('adminhtml/session')
                    ->settestData($this->getRequest()
                            ->getPost()
                    );
                $this->_redirect('*/*/edit',
                    array('id' => $this->getRequest()
                            ->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Method for delete Shop Section
     */
    public function deleteAction()
    {
        if(!$this->verifyEtsyApi()){ return; }

        if($this->getRequest()->getParam('id') > 0)
        {
            try
            {
                $shopSectionModel = Mage::getModel('magetsync/shopSection');
                $shopValue = $shopSectionModel->load($this->getRequest()->getParam('id'));
                $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
                $obligatory = array('shop_id'=>$shop);
                $obligatory['shop_section_id'] = $shopValue['shop_section_id'];
                $resultApi = $shopSectionModel->deleteShopSection($obligatory,null);
                if($resultApi['status']=!false) {
                    $shopSectionModel->setId($this->getRequest()
                        ->getParam('id'))
                        ->delete();

                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('magetsync')->__('Successfully deleted'));
                }
                else
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($resultApi['message']);
                }
                $this->_redirect('*/*/');
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Method for verify if the api connection is correct
     * @return bool
     */
    public function verifyEtsyApi()
    {
        if(Mage::getModel('magetsync/etsy')->load(1)->getData('AccessToken') <> '')
        {
            return true;
        }
        else
        {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('magetsync')->__('First you must authorise access to Etsy under System > Configuration > MagetSync'));
            $this->_redirect('*/*/');
            return false;
        }
    }
}