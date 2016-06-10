<?php
error_reporting(E_ALL ^ E_NOTICE);

class Merchante_MagetSync_Adminhtml_Magetsync_ShippingTemplateController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('magetsync/shippingTemplate')
            ->_addBreadcrumb('MagetSync Manager','MagetSync Manager');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $shippingId = $this->getRequest()->getParam('id');
        $shippingModelData = Mage::getModel('magetsync/shippingTemplate')->load($shippingId);
        if ($shippingModelData->getId() <> null || $shippingId == null)
        {
            if($shippingModelData->getId())
            {
                $shippingModelData->setEntries($shippingModelData->getShippingTemplateId());
                $processingModel = Mage::getModel('magetsync/processingTime')->load($this->getRequest()->getParam('processing'));
                $shippingModelData['max_processing_days']            = $processingModel['max'];
                $shippingModelData['min_processing_days']            = $processingModel['min'];
                $shippingModelData['processing_days_display_label']  = $processingModel['label'];
            }
            Mage::register('magetsync_shippingtemplate', $shippingModelData);
            $this->loadLayout();
            $this->_setActiveMenu('magetsync/shippingTemplate');
            $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()
                ->createBlock('magetsync/adminhtml_global_shippingTemplate_edit'))
                ->_addLeft($this->getLayout()
                        ->createBlock('magetsync/adminhtml_global_shippingTemplate_edit_tabs')
                );
            $this->renderLayout();
        }
        else
        {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('magetsync')->__('Shipping profile does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function syncAction()
    {
        try {
            $shippingTemplateModel = Mage::getModel('magetsync/shippingTemplate');
            $userModel = Mage::getModel('magetsync/user');
            $userCollection = $userModel->getUser(array('user_id'=>'__SELF__'));
            $resultUser = json_decode(json_decode($userCollection['result']), true);
            $resultUser = $resultUser['results'][0];
            $obligatory = array('user_id' =>$resultUser['user_id']);
            $dataApi = $shippingTemplateModel->findAllUserShippingProfiles($obligatory, null);
            if ($dataApi['status'] == true) {
                $results = json_decode(json_decode($dataApi['result']), true);
                $results = $results['results'];
                foreach ($results as $value) {
                    $shippingCollection = Mage::getModel('magetsync/shippingTemplate')->getCollection();
                    $queryShipping = $shippingCollection->getSelect()->where('shipping_template_id = ?', $value['shipping_template_id']);
                    $queryShipping = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryShipping);
                    if (!$queryShipping) {
                        $processingCollection = Mage::getModel('magetsync/processingTime')->getCollection();
                        $queryProcessing= $processingCollection->getSelect()->where('label = ?', $value['processing_days_display_label']);
                        $queryProcessing = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryProcessing);
                        if($queryProcessing) {
                            $value['processing'] =$queryProcessing[0]['id'];
                        }else{
                            $value['processing'] = null;
                        }
                        $shippingTemplateModel
                            ->addData($value)
                            ->setId($queryShipping[0]['id'])
                            ->save();

                        $shippingTemplateModel = Mage::getModel('magetsync/shippingTemplate');
                        $obligatory = array('shipping_template_id' => $value['shipping_template_id']);
                        $dataApiEntry = $shippingTemplateModel->findAllShippingTemplateEntries($obligatory, null);
                        $results = json_decode(json_decode($dataApiEntry['result']), true);
                        $results = $results['results'];
                        foreach ($results as $value) {
                            $entryTemplateModel = Mage::getModel('magetsync/shippingEntry');
                            $entryTemplateModel
                                ->addData($value)
                                ->save();
                        }

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
            Mage::log("Error: ".print_r($e, true),null,'shippingtemplate.log');
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

    public function saveAction()
    {
        if(!$this->verifyEtsyApi()){ return; }

        if ($this->getRequest()->getPost())
        {
            try {
                $shippingTemplateModel = Mage::getModel('magetsync/shippingTemplate');
                $shippingEntryModel = Mage::getModel('magetsync/shippingEntry');
                $params = array();
                if($this->getRequest()->getParam('origin_country_id'))
                {
                    $params['origin_country_id'] = $this->getRequest()->getParam('origin_country_id');
                }
                if($this->getRequest()->getParam('title'))
                {
                    $params['title'] = $this->getRequest()->getParam('title');
                }

                $postData = $this->getRequest()->getPost();

                $language = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_language');
                if($language <> null) { $params['language']= $language;}
                else{ throw new Exception(Mage::helper('magetsync')->__('Must configure Etsy\'s language')); }

                if(count($postData['shipping_entry']['value'])== 0){
                    throw new Exception(Mage::helper('magetsync')->__('Must select least one destination')); }

                $freq = $this->getRequest()->getParam('frequency');
                $labelFreq = '';
                if($freq == 'W') {
                    $labelFreq = Mage::helper('magetsync')->__("weeks");
                }
                if($freq == 'D') {
                    $labelFreq = Mage::helper('magetsync')->__("business days");
                }

                if($this->getRequest()->getParam('processing') <> null && $this->getRequest()->getParam('processing') <> -2)
                {
                    $processingModel = Mage::getModel('magetsync/processingTime')->load($this->getRequest()->getParam('processing'));
                    $params['max_processing_days']            = $processingModel['max'];
                    $params['min_processing_days']            = $processingModel['min'];
                    $params['processing_days_display_label']  = $processingModel['label'];
                }
                else
                {
                    if($this->getRequest()->getParam('min_processing_days'))
                    {
                        $params['min_processing_days'] = $this->getRequest()->getParam('min_processing_days');
                    }
                    if($this->getRequest()->getParam('max_processing_days'))
                    {
                        $params['max_processing_days'] = $this->getRequest()->getParam('max_processing_days');
                    }

                    if($freq)
                    {
                        $params['processing_days_display_label'] = $params['min_processing_days'] ."-".$params['max_processing_days']." ".$labelFreq;
                    }
                    $processingModel = Mage::getModel('magetsync/processingTime');
                    $processingCollection = Mage::getModel('magetsync/processingTime')->getCollection();
                    $query = $processingCollection->getSelect()->where('label = ?', $params['processing_days_display_label']);
                    $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                    if($query == null)
                    {
                        $processingArray = array();
                        $processingArray['label']     = $params['processing_days_display_label'];
                        $processingArray['max']       = $params['max_processing_days'];
                        $processingArray['min']       = $params['min_processing_days'];
                        $processingArray['frequency'] = $labelFreq;
                        $processingModel
                        ->addData($processingArray)
                        ->setId(null);
                        $postData['processing'] = $processingModel->save()->getId();
                    }
                }


                $result = null;
                $control = 0;
                if( $this->getRequest()->getParam('id') <= 0 )
                {
                    if($postData['shipping_entry']['value'] <> null && count($postData['shipping_entry']['value']) > 0)
                    {
                        $dataValue = $postData['shipping_entry']['value']['option_0'];
                        if($dataValue)
                        {
                            $params['destination_country_id'] = (int)($dataValue['countryDestination']['country_id']);
                            $params['primary_cost']           = $dataValue['primary_cost'];
                            $params['secondary_cost']         = $dataValue['secondary_cost'];
                        }
                    }

                    $resultApi = $shippingTemplateModel->createShippingTemplate(null,$params);
                    if($resultApi['status'] == true)
                    {
                        $result = json_decode(json_decode($resultApi['result']),true);
                        $result = $result['results'][0];
                    }
                    $control = 1;
                }
                else
                {
                    $obligatory['shipping_template_id'] = $this->getRequest()->getParam('shipping_template_id');
                    $resultApi = $shippingTemplateModel->updateShippingTemplate($obligatory,$params);
                    if($resultApi['status'] == true)
                    {
                        $result = json_decode(json_decode($resultApi['result']),true);
                        $result = $result['results'][0];
                    }
                    $shippingTemplateModel->load($this->getRequest()->getParam('id'));
                }

                $postData['shipping_template_id']          =  (string)$result['shipping_template_id'];
                $postData['title']                         =  $result['title'];
                $postData['min_processing_days']           =  $result['min_processing_days'];
                $postData['max_processing_days']           =  $result['max_processing_days'];
                $postData['processing_days_display_label'] =  $result['processing_days_display_label'];
                $postData['origin_country_id']             =  $result['origin_country_id'];
                $postData['user_id']                       =  $result['user_id'];


                if($resultApi['status'] != false) {
                    if ($this->getRequest()->getParam('id') > 0) {
                        $shippingTemplateModel->addData($postData);
                    } else {
                        $shippingTemplateModel->setData($postData);
                    }
                    $shippingTemplateModel->save();

                    $countryArray = array();
                    $i = 0;
                    foreach ($postData['shipping_entry']['value'] as $value) {
                        $obligatory = array('shipping_template_entry_id' => $value['shipping_template_entry_id']);
                        $paramsEntry = array();
                        $paramsEntry['destination_country_id'] = (int)($value['countryDestination']['country_id']);
                        $paramsEntry['shipping_template_entry_id'] = (string)$value['shipping_template_entry_id'];
                        $paramsEntry['primary_cost'] = $value['primary_cost'];
                        $paramsEntry['secondary_cost'] = $value['secondary_cost'];
                        $paramsEntry['shipping_template_id'] = (string)$result['shipping_template_id'];


                        $valSearch = array_search((int)($value['countryDestination']['country_id']), $countryArray);
                        $countryArray[] = ($value['countryDestination']['country_id']);
                        if ($valSearch !== false) {
                            $i++;
                            continue;
                        }

                        $idValue = ($value['id'] == "") ? null : $value['id'];
                        if ($control == 1) {
                            if ($i == 0) {

                                $obligatory = array('shipping_template_id' => $result['shipping_template_id']);
                                $resultEntriesApi = $shippingTemplateModel->findAllShippingTemplateEntries($obligatory, null);
                                if($resultEntriesApi['status'] == true) {
                                    $resultEntries = json_decode(json_decode($resultEntriesApi['result']), true);
                                    $resultEntries = $resultEntries['results'][0];
                                    $paramsEntry['shipping_template_entry_id'] = (string)$resultEntries['shipping_template_entry_id'];

                                    $shippingEntryModel
                                        ->addData($paramsEntry)
                                        ->setId($idValue)
                                        ->save();
                                }
                                $i++;
                                continue;
                            }
                        }
                        if ($value['shipping_template_entry_id'] <> null) {
                            $resultEntryApi = $shippingEntryModel->updateShippingTemplateEntry($obligatory, $paramsEntry);
                            if($resultEntryApi['status'] == true) {
                                $resultEntry = json_decode(json_decode($resultEntryApi['result']), true);
                                $resultEntry = $resultEntry['results'][0];
                            }
                        } else {
                            $resultEntryApi = $shippingEntryModel->createShippingTemplateEntry(null, $paramsEntry);
                            if($resultEntryApi['status'] == true) {
                                $resultEntry = json_decode(json_decode($resultEntryApi['result']), true);
                                $resultEntry = $resultEntry['results'][0];
                            }
                            $paramsEntry['shipping_template_entry_id'] = (string)$resultEntry['shipping_template_entry_id'];
                        }

                        if($resultEntryApi['status'] == true) {
                            $shippingEntryModel
                                ->addData($paramsEntry)
                                ->setId($idValue)
                                ->save();
                        }
                    }

                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('magetsync')->__('Successfully saved'));
                    Mage::getSingleton('adminhtml/session')
                        ->settestData(false);
                }
                else
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($resultApi['message']);
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e){
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
                Mage::log("Error: ".print_r($e, true),null,'shippingtemplate.log');
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

    public function deleteAction()
    {
        if(!$this->verifyEtsyApi()){ return; }

        if($this->getRequest()->getParam('id') > 0)
        {
            try
            {
                $shippingModel = Mage::getModel('magetsync/shippingTemplate');
                $shippingValue = $shippingModel->load($this->getRequest()->getParam('id'));
                $idShipping = $shippingValue->getShippingTemplateId();
                $obligatory = array('shipping_template_id'=>$idShipping);
                $resultApi = $shippingModel->deleteShippingTemplate($obligatory,null);
                if($resultApi['status']=!false) {

                    $shippingModel->setId($this->getRequest()
                        ->getParam('id'))
                        ->delete();
                    $shippingEntryModel = Mage::getModel('magetsync/shippingEntry');
                    $shippingQuery = $shippingEntryModel->getCollection();
                    $shippingQuery = $shippingQuery->getSelect()->where('shipping_template_id = ?', $idShipping);
                    $shippingQuery = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($shippingQuery);

                    for ($i = 0; $i <= count($shippingQuery); $i++) {
                        $shippingEntryModel->setId($shippingQuery[$i]['id'])->delete();
                    }

                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('magetsync')->__('Successfully deleted'));
                }else
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