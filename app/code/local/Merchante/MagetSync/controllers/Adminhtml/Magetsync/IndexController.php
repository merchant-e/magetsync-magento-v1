<?php
error_reporting(E_ALL ^ E_NOTICE);

    /**
     * @copyright  Copyright (c) 2015 Merchant-e
     *
     * Main Controller section adminhtml
     * Class Merchante_Magetsync_Adminhtml_IndexController
     */
    class Merchante_MagetSync_Adminhtml_Magetsync_IndexController extends Mage_Adminhtml_Controller_Action
    {
        /**
         * Method initAction
         * @return $this
         */
        protected function _initAction()
        {
            $this->loadLayout()->_setActiveMenu('magetsync/listing')
                        ->_addBreadcrumb('MagetSync Manager','MagetSync Manager');

           return $this;
         }

        /**
         * Method for rendering Layout
         */
        public function indexAction()
          {
             $this->_initAction();
             $this->renderLayout();
          }

        /**
         * Method for massive updating of listings
         */
        public function updateattributesAction() {
             $data = $this->getRequest()->getPost();
            if(count($data['listingids']) > 1) {
                Mage::unregister('magetsync_massive');
                $string = implode(',', $data['listingids']);
                Mage::register('magetsync_massive', $string);
                $this->editAction();
            }else{
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('magetsync')->__("You should select more than one listing."));
                $this->_redirect('*/*/');
            }
         }

        /**
         * Method for checking a listing and mark as Sync
         */
        public function sendtoetsyAction() {
            try{
            if(!$this->verifyEtsyApi()){ return; }
            $data = $this->getRequest()->getPost();
             $listingModel = Mage::getModel('magetsync/listing');
             $listings = $listingModel->getCollection()->addFieldToSelect('sync')->addFieldToSelect('id')->addFieldToFilter('id',array('in'=>$data['listingids']))->load()->toArray();
             foreach ($listings['items'] as $value) {
                 //$listing = $listingModel->load($value);
                 $sync = $value['sync'];
                 if($sync == Merchante_MagetSync_Model_Listing::STATE_INQUEUE || $sync == Merchante_MagetSync_Model_Listing::STATE_FAILED  ||
                     $sync == Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC || $sync== Merchante_MagetSync_Model_Listing::STATE_MAPPED)
                 {
                   $this->saveAction(1,$value['id'],1);
                 }
             }
             $this->_redirect('adminhtml/magetsync_index/index');
             return;
            }catch (Exception $e)
            {
                Mage::logException($e);
                return;
            }
         }

        /**
         * Method for creating and listing categories
         */
        public function categoryAction() {
            $tag = $this->getRequest()->getParam('tag');
            $state = "<option value=''>".Mage::helper('magetsync')->__('Please Select')."</option>";
            $controlAux = 0;
            if ($tag != '') {
            $subCategories = Mage::getModel('magetsync/category')->getCollection();
            $query = $subCategories->getSelect()->where('parent_id= ?',$tag);
            $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
            foreach ($query as $subCategory) {
                $state .= "<option value=".$subCategory['level_id'].">" . $subCategory['short_name'] . "</option>";
                $controlAux = 1;
            }
            }
            if($controlAux == 0)
            {echo '';}else{echo $state;}
           }

        /**
         * Method edit for listing
         */
        public function editAction()
          {
               if($this->getRequest()->getParam('id')== null){ $testId = 0; }
               else { $testId = $this->getRequest()->getParam('id'); }
               $testModel = Mage::getModel('magetsync/listing')->load($testId);
               if ($testModel->getId() || $testId == 0)
               {
                 Mage::register('magetsync_data', $testModel);
                 $this->loadLayout();
                 $this->_setActiveMenu('magetsync/set_time');
                 $this->_addBreadcrumb('MagetSync Manager','MagetSync Manager');
                 $this->_addBreadcrumb('MagetSync Description','MagetSync Description');
                 $this->getLayout()->getBlock('head')
                      ->setCanLoadExtJs(true);
                 $this->_addContent($this->getLayout()
                      ->createBlock('magetsync/adminhtml_listing_edit'))
                      ->_addLeft($this->getLayout()
                      ->createBlock('magetsync/adminhtml_listing_edit_tabs')
                  );
                 $this->renderLayout();
               }
               else
               {
                     Mage::getSingleton('adminhtml/session')
                           ->addError(Mage::helper('magetsync')->__("Listing does not exist"));
                     $this->_redirect('*/*/');
                }
           }

        function listingDeleteCallback($args){
            $listing = Mage::getModel('magetsync/listing');
            $listing->setData($args['row']);
            $listing->delete();
        }

        function updateAttributeCallback($args){
            $product = Mage::getModel('catalog/product');
            $product->setData($args['row']);
            $product->setData('synchronizedEtsy', false)->getResource()->saveAttribute($product, 'synchronizedEtsy');
        }

        /**
         * Method for deleting all listings
         */
        public function deleteAllListingsAction()
        {
            try {

                $collection = Mage::getModel('magetsync/listing')->getCollection()
                    ->addFieldToSelect('id');
                Mage::getSingleton('core/resource_iterator')->walk($collection->getSelect(), array(array($this, 'listingDeleteCallback')));

                $collectionProduct = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('synchronizedEtsy')
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable')))
                    ->addAttributeToFilter('synchronizedEtsy', array('eq' => true), 'left');
                Mage::getSingleton('core/resource_iterator')->walk($collectionProduct->getSelect(), array(array($this, 'updateAttributeCallback')));

                $result = array('success' => true);
                echo json_encode($result,true);

            }catch(Exception $e)
            {
                $result = array('success' => false,'msg' => $e->getMessage());
                echo json_encode($result,true);
            }
        }

        /**
         * Method for forcing delete listing
         */
        public function forceDeleteAction()
        {
            try {
                $entity_id = $this->getRequest()
                    ->getParam('entity_id');
                $listing_id = $this->getRequest()
                    ->getParam('listing_id');
                $listingData = Mage::getModel('magetsync/listing')->load($entity_id);
                $idproduct = $listingData['idproduct'];
                Mage::getModel('magetsync/listing')->setId($entity_id)
                                       ->delete();

                $imageModel = Mage::getModel('magetsync/imageEtsy')
                    ->getCollection()
                    ->addFieldToFilter('listing_id', array('eq'=>$listing_id));
                foreach ($imageModel as $img) {
                    $img->delete();
                }

                $logModel = Mage::getModel('magetsync/logData')
                    ->getCollection()
                    ->addFieldToFilter('entity_id', array('eq'=>$entity_id));
                foreach ($logModel as $log) {
                    $log->delete();
                }

                $product = Mage::getModel('catalog/product')->load($idproduct);

                if($product) {
                    $product->setData('synchronizedEtsy', false)->getResource()->saveAttribute($product, 'synchronizedEtsy');
                }

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('magetsync')->__('Listing successfully deleted'));

                $result = array('success' => true);
                echo json_encode($result,true);

            }catch(Exception $e)
            {
                $result = array('success' => false,'msg' => $e->getMessage());
                echo json_encode($result,true);
            }
        }

        /**
         * Method for importing orders from Etsy with was-shipped status
         */
        public function importOrdersAction()
        {
            try {

                $orderModel = Mage::getModel('magetsync/order');
                $result = $orderModel->makeOrder(1);
                echo json_encode($result,true);

            }catch(Exception $e)
            {
                $result = array('status' => false,'message' => $e->getMessage());
                echo json_encode($result,true);
                //return;
            }
        }

        /**
         * Method redirect new action to edit action
         */
        public function newAction()
           {
              $this->_forward('edit');
           }

        /**
         * Method save for listing
         * @param int $actionStatus
         * @param int $listingId
         * @param int $isSendtoEtsy
         */
        public function saveAction($actionStatus = 0,$listingId = 0,$isSendtoEtsy = 0)
           {

             if(!$this->verifyEtsyApi()){ return; }

             if ($this->getRequest()->getPost())
             {
               if($actionStatus == 0)
               { $syncStatus =  $this->getRequest()->getParam('syncStatus'); }
               else{ $syncStatus = $actionStatus; }

               $dataGlobal    = '';
               try {
                     $postData = $this->getRequest()->getPost();
                     $listingModel = Mage::getModel('magetsync/listing');
                   if($listingId == 0) {
                       if ($postData['listingids'] <> null) {
                           if (is_array($postData['listingids'])) {
                               $newListing = $postData['listingids'];
                           } else {
                               $newListing = explode(',', $postData['listingids']);
                           }
                       } else {
                           $newListing = array($this->getRequest()->getParam('id'));
                       }
                   }else{
                       $newListing = array($listingId);
                   }
                   $languageData = '';
                   $language = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_language');
                   if($language <> null) { $languageData= $language;}
                   else{ throw new Exception(Mage::helper('magetsync')->__('Must configure Etsy\'s language')); }


                   //$products = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*')->addIdFilter($newListing)->load();

                   $listingModel = Mage::getModel('magetsync/listing');
                   $listings = $listingModel->getCollection()->addFieldToSelect('*')->addFieldToFilter('id',array('in'=>$newListing))->load();//->toArray();

                   foreach ($listings as $value) {

                       $data =  $value->getData(); //$listingModel->load($value)->getData();
                       //$data['quantity'] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($value)->getQty();

                       if(isset($postData['category_id']))
                       {
                           $postData['category_id']       = $listingModel->emptyField($postData['category_id'],null);
                           $postData['subcategory_id']    = $listingModel->emptyField($postData['subcategory_id'],null);
                           $postData['subsubcategory_id'] = $listingModel->emptyField($postData['subsubcategory_id'],null);
                           $postData['subcategory4_id']   = $listingModel->emptyField($postData['subcategory4_id'],null);
                           $postData['subcategory5_id']   = $listingModel->emptyField($postData['subcategory5_id'],null);
                           $postData['subcategory6_id']   = $listingModel->emptyField($postData['subcategory6_id'],null);
                           $postData['subcategory7_id']   = $listingModel->emptyField($postData['subcategory7_id'],null);
                       }

                       if(isset($postData['style_one']))
                       {
                           $postData['style_one']   = $listingModel->emptyField($postData['style_one'],null);
                           $postData['style_two']   = $listingModel->emptyField($postData['style_two'],null);
                       }

                       /********UPDATE*********/

                       if($data['listing_id'] && $syncStatus) {
                           if (!$isSendtoEtsy) {
                               $value
                                   ->addData($postData);
                                   //->setId($value);
                                   //->setId($data['id']);
                               $updateProduct = $value->save();
                               $data = $updateProduct->getData();//$listingModel->load($value)->getData();
                           }
                       }

                       $supply = $listingModel->emptyField($postData['is_supply'],$data['is_supply']);

                       if($supply == 1){
                           $dataSuppley = 0;
                       }else{$dataSuppley = 1;}

                       $taxonomyID = $listingModel->getTaxonomyID($postData,$data);

                       $prependedTemplate = $listingModel->emptyField($postData['prepended_template'],$data['prepended_template']);
                       $appendedTemplate  = $listingModel->emptyField($postData['appended_template'],$data['appended_template']);

                       $newDescription = $listingModel->composeDescription($data['description'],$prependedTemplate,$appendedTemplate);
                       $renewalOption  = $listingModel->emptyField($postData['should_auto_renew'],$data['should_auto_renew'],0);

                      $style = array();
                      $style[] = $listingModel->emptyField($postData['style_one'],$data['style_one']);
                      $style[] = $listingModel->emptyField($postData['style_two'],$data['style_two']);

                      $styleData  = implode(',',$style);

                       if(isset($data['tags']))
                       {
                           $search  = array(';', '.', '/', '\\');
                           $data['tags'] = str_replace($search, '', $data['tags']);
                           $newTagsAux = explode(',',strtolower($data['tags']));
                           $newTags = array_unique($newTagsAux);
                           $data['tags'] = implode(',',$newTags);
                       }

                       $stateListing     = Merchante_MagetSync_Model_Listing::STATE_ACTIVE;
                       if($data['quantity'] >999)
                       {
                           $data['quantity'] = 999;
                       }
                       elseif($data['quantity'] == 0)
                       {
                           $stateListing     = Merchante_MagetSync_Model_Listing::STATE_INACTIVE;
                           $data['quantity'] = 1;
                       }

                       $params = array(
                          'description' =>         $newDescription,//$data['description'],
                          'materials' =>           $listingModel->emptyField($postData['materials'],$data['materials']),
                          'state'     =>           $stateListing,
                          //'price'=>              $data['price'],
                          'quantity'=>             $data['quantity'],
                          'shipping_template_id'=> $listingModel->emptyField($postData['shipping_template_id'],$data['shipping_template_id']),
                          'shop_section_id'=>      $listingModel->emptyField($postData['shop_section_id'],$data['shop_section_id']),
                          'title'=>                $data['title'],
                          'tags'=>                 $data['tags'],
                          'taxonomy_id'=>          $taxonomyID,
                          'who_made'=>             $listingModel->emptyField($postData['who_made'],$data['who_made']),
                          'is_supply'=>            $dataSuppley,
                          'when_made'=>            $listingModel->emptyField($postData['when_made'],$data['when_made']),
                          'recipient'=>            $listingModel->emptyField($postData['recipient'],$data['recipient']),
                          'occasion'=>             $listingModel->emptyField($postData['occasion'],$data['occasion']),
                          'style'=>                $styleData,
                          'should_auto_renew'=>    $renewalOption,
                          'language'=>             $languageData);
                       $dataGlobal    = $data['id'];//$value;
                       $hasError = false;
                       if($syncStatus) {

                           if($postData && array_key_exists('price',$postData)) {
                               $priceEtsy = $postData['price'];
                           }else{
                               $priceEtsy = $data['price'];
                           }

                           if ($data['listing_id']) {
                               $obliUpd = array('listing_id' => $data['listing_id']);
                               $resultApi = $listingModel->updateListing($obliUpd, $params);
                           } else {
                               $new_pricing = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_enable_different_pricing');
                               if($new_pricing) {
                                   $params['price'] = $priceEtsy;
                               }else {
                                   $params['price'] = $data['price'];
                               }
                               $resultApi = $listingModel->createListing(null, $params);
                           }
                           if ($resultApi['status'] == true) {

                               $result = json_decode(json_decode($resultApi['result']), true);
                               $result = $result['results'][0];
                               $statusOperation = $listingModel->saveDetails($result,$data['idproduct'],$priceEtsy,$dataGlobal);
                               /*********************************/

                               $postData['creation_tsz'] = $result['creation_tsz'];
                               $postData['ending_tsz'] = $result['ending_tsz'];
                               $postData['original_creation_tsz'] = $result['original_creation_tsz'];
                               $postData['last_modified_tsz'] = $result['last_modified_tsz'];
                               $postData['currency_code'] = $result['currency_code'];
                               $postData['featured_rank'] = $result['featured_rank'];
                               $postData['url'] = $result['url'];
                               $postData['views'] = $result['views'];
                               $postData['num_favorers'] = $result['num_favorers'];
                               $postData['processing_min'] = $result['processing_min'];
                               $postData['processing_max'] = $result['processing_max'];
                               $postData['non_taxable'] = $result['non_taxable'];
                               $postData['is_customizable'] = $result['is_customizable'];
                               $postData['is_digital'] = $result['is_digital'];
                               $postData['file_data'] = $result['file_data'];
                               $postData['has_variations'] = $result['has_variations'];
                               $postData['language'] = $result['language'];
                               $postData['listing_id'] = $result['listing_id'];
                               $postData['state'] = $result['state'];
                               $postData['user_id'] = $result['user_id'];
                               //$postData['should_auto_renew'] = $result['should_auto_renew'];

                               if($statusOperation['status']) {
                                   if ($result['state'] == 'edit') {
                                       $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
                                   } else {
                                       $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
                                   }
                               }else{

                                   $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                                   $hasError = true;
                                   if($statusOperation['message'])
                                   {
                                       $resultApi['message'] =$statusOperation['message'];
                                   }else {
                                       $resultApi['message'] = Mage::helper('magetsync')->__('Error processing details');
                                   }
                               }
                           } else {
                               $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                               $hasError = true;
                               if (strpos($resultApi['message'], 'The listing is not editable, must be active or expired but is removed') !== false)
                               {
                                   $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
                               }
                           }

                       }else
                       {
                           if($data['listing_id'])
                           {
                               $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC;
                           }else{
                               $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_INQUEUE;
                           }
                       }

                       $value
                           ->addData($postData);
                           //->setId($value);
                           //->setId($dataGlobal);
                       $value->save();

                       if($hasError == true)
                       {
                           Merchante_MagetSync_Model_LogData::magetsync($dataGlobal,Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                               $resultApi['message'],Merchante_MagetSync_Model_LogData::LEVEL_ERROR);

                           Mage::getSingleton('adminhtml/session')
                           ->addError($resultApi['message']);
                       }else{
                           if(!$syncStatus) {
                               Mage::getSingleton('adminhtml/session')
                                   ->addSuccess(Mage::helper('magetsync')->__('Successfully saved'));
                           }else{
                               Mage::getSingleton('adminhtml/session')
                                   ->addSuccess(Mage::helper('magetsync')->__('Successfully synchronized'));
                           }

                           /**********CLEAN LOGS*********/
                           $logData = Mage::getModel('magetsync/logData');
                           $logData->remove($dataGlobal,Merchante_MagetSync_Model_LogData::TYPE_LISTING);
                           /****************************/
                       }
                   }

                     Mage::getSingleton('adminhtml/session')
                                    ->settestData(false);
                     $this->_redirect('*/*/');
                    return;
               }catch (Exception $e){
                   if($e instanceof OAuthException)
                   {
                       Mage::getSingleton('adminhtml/session')
                           ->addError($e->lastResponse);
                       $errorMsg = $e->lastResponse;
                   }
                   else
                   {
                       Mage::getSingleton('adminhtml/session')
                           ->addError($e->getMessage());
                       $errorMsg = $e->getMessage();
                   }
                   Mage::log("Error: ".print_r($errorMsg, true),null,'listing.log');
                    Mage::getSingleton('adminhtml/session')
                     ->settestData($this->getRequest()
                                        ->getPost()
                    );
                   if($dataGlobal) {

                       Merchante_MagetSync_Model_LogData::magetsync($dataGlobal,Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                           $errorMsg,Merchante_MagetSync_Model_LogData::LEVEL_ERROR);

                       $listingModel = Mage::getModel('magetsync/listing')->load($dataGlobal);
                           $listingModel->addData(array('sync' => null))
                           ->setId($dataGlobal)
                           ->save();
                   }
                    $this->_redirect('*/*/edit',
                                array('id' => $dataGlobal));//$this->getRequest() ->getParam('listing_id')));
                    return;
                    }
                  }
                  //$this->_redirect('*/*/');
                }


        /**
         * Method delete for listing
         */
        public function deleteAction()
        {
            if(!$this->verifyEtsyApi()){ return; }
            $value = $this->getRequest()->getParam('id');
            $listingModel = Mage::getModel('magetsync/listing');
            $data =  $listingModel->load($value)->getData();
            try
            {
              if($data['listing_id']) {
                  $resultApi['status'] = false;
                  $resultApi['message'] = Mage::helper('magetsync')->__('You can not delete this listing because it is already synchronized');
              }else{ $resultApi['status'] = true; }
                if($resultApi['status'] == true) {
                    $listingModel->setId($this->getRequest()
                        ->getParam('id'))
                        ->delete();
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
                $this->_redirect('*/*/edit', array('listing_id' => $this->getRequest()->getParam('listing_id')));
            }
             $this->_redirect('*/*/');
        }

        /**
         * Method for verifying if the api connection is correct
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

    ?>