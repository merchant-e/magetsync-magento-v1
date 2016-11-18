<?php
//error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Observer
 */
class Merchante_MagetSync_Model_Observer
{
    /**
     * Number of products sent to Etsy per cron job
     */
    const AUTOQUEUE_ITERATIONS_LIMIT = 3;

    /**
     * @param $observer
     */
    public function saveTrack($observer)
    {
        try
        {
        $info = $observer->getData();
        $info = $info['track'];
        $carrier = $info->getData();
        $carrier = $carrier['carrier_code'];
        $track_number = $info->getData();
        $track_number = $track_number['track_number'];
        $orderId = $observer->getEvent()->getTrack()->getOrderId();
        if($carrier == 'australiapost')
        {
            $carrier = 'australia-post';
        }
        $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
        $receipt = Mage::getModel('magetsync/receipt');
        $orderECollection = Mage::getModel('magetsync/orderEtsy')->getCollection();
        $query = $orderECollection->getSelect()->where('order_id = ?',$orderId);
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        if($query) {
            $obligatory = array('shop_id' => $shop,'receipt_id' => $query[0]['receipt_id']);
            $params = array('tracking_code' => $track_number, 'carrier_name' => $carrier);
            $dataApi = $receipt->submitTracking($obligatory, $params);
            if ($dataApi['status'] == true) {
                //$results = json_decode(json_decode($dataApi['result']), true);
                //$results = $results['results'];
            } else {
                Mage::log("Error: " . print_r($dataApi['message'], true), null, 'magetsync_track.log');
            }
        }
        return;
        } catch (Exception $e){
            Mage::log("Error: ".print_r($e, true),null,'magetsync_track.log');
            return;
        }
    }

    public function setOptionArray($values,$arrOptions,$optionID,$option_type_id = 'option_type_id')
    {
        if ($values) {
            $first = reset($values);
            $arrOptions[$optionID] = $first[$option_type_id];
        } else {
            $arrOptions[$optionID] = '0';
        }
        return $arrOptions;
    }

    /**
     * @param $observer
     * @return boolean
     */
    public function createOrder($observer)
    {
       try {
           $orderModel = Mage::getModel('magetsync/order');
           return $orderModel->makeOrder();
       }catch (Exception $e)
        {
            Mage::log("Error: " . print_r($e->getMessage(), true), null, 'magetsync_order.log');
            return false;
        }
    }

    public function checkListingsExpired($observer)
    {
        $etsyModel = Mage::getModel('magetsync/etsy');
        if ($etsyModel->verifyDataConfiguration()) {
            $listingModel = Mage::getModel('magetsync/listing');
            $query = $listingModel->getCollection()->getSelect()->where('(sync =' . Merchante_MagetSync_Model_Listing::SYNCED .
                ' OR sync =' . Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC
                . ') AND enabled =' . Merchante_MagetSync_Model_Listing::LISTING_ENABLED);
            $results = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
            foreach ($results as $item) {

                $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
                if ($shop) {
                    $obliUpd = array('listing_id' => $item['listing_id'], 'shop_id' => $shop);
                    $resultApiGet = $listingModel->getShopListingExpired($obliUpd, null);
                    if ($resultApiGet['status'] == true) {
                        $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
                        $listingModel
                            ->addData($postData)
                            ->setId($item['id']);
                        $listingModel->save();
                    } else {
                        Merchante_MagetSync_Model_LogData::magetsync($item['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                            $resultApiGet['message'], Merchante_MagetSync_Model_LogData::LEVEL_WARNING);
                    }
                }
            }
        }

    }

    public function checkInventory($observer)
    {
        $etsyModel = Mage::getModel('magetsync/etsy');
        if ($etsyModel->verifyDataConfiguration()) {
            $listingModel = Mage::getModel('magetsync/listing');
            $query = $listingModel->getCollection()->getSelect()->where('(quantity_has_changed =' . Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED .
                ' OR sync =' . Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC
                . ') AND enabled =' . Merchante_MagetSync_Model_Listing::LISTING_ENABLED . ' AND sync !=' . Merchante_MagetSync_Model_Listing::STATE_INQUEUE . ' AND sync !='
                . Merchante_MagetSync_Model_Listing::STATE_FAILED . ' AND sync !=' . Merchante_MagetSync_Model_Listing::STATE_EXPIRED.
                ' AND sync !=' . Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE);
            $results = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
            foreach ($results as $item) {
                $product = Mage::getModel('catalog/product')->load($item['idproduct']);
                $dataProduct = $product->getData();
                $qty = 0;
                $params = array();
                $postData = array();
                if ($product['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                    /*if($product['is_in_stock'])
                    {
                        $qty = $product->getStockItem()->getQty();
                    }else {
                        $qty = 0;
                    }*/
                    $stock = $product->getStockItem();
                    if ($stock->getIsInStock()) {
                        $qty = $stock->getQty();
                    } else {
                        $qty = 0;
                    }

                } elseif ($dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    foreach ($product->getTypeInstance(true)->getUsedProducts(null, $product) as $simple) {
                        if ($simple->getData('is_in_stock')) {
                            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simple)->getQty();
                            $stock = round($stock, 2);
                            $qty += $stock;
                        } else {
                            $qty += 0;
                        }
                    }
                }

                $autoSync = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_auto_sync');
                if ($autoSync == '1') {
                    if ($item['sync'] == Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC) {

                        $taxonomyID = $listingModel->getTaxonomyID($item, null);

                        $newDescription = $listingModel->composeDescription($item['description'], $item['prepended_template'], $item['appended_template']);
                        $style = array();
                        $style[] = $item['style_one'];
                        $style[] = $item['style_two'];

                        $styleData = implode(',', $style);

                        $supply = isset($item['is_supply']) ? $item['is_supply'] : 1;
                        //Boolean field in Etsy but not 'Yes/No' in frontEnd
                        if ($supply == 1) {
                            $dataSuppley = 0;
                        } else {
                            $dataSuppley = 1;
                        }
                        $renewalOption = (isset($item['should_auto_renew']) && $item['should_auto_renew']) ? $item['should_auto_renew'] : 0;

                        if (isset($item['tags'])) {
                            $search = array(';', '.', '/', '\\');
                            $item['tags'] = str_replace($search, '', $item['tags']);
                            $newTagsAux = explode(',', strtolower($item['tags']));
                            $newTags = array_unique($newTagsAux);
                            $item['tags'] = implode(',', $newTags);
                        }

                        $new_pricing = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_enable_different_pricing');
                        if (!$new_pricing) {
                            $today = new DateTime("now");
                            if ($dataProduct['special_price'] != '') {
                                $useSpecialPrice = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_special_price');
                                if ($useSpecialPrice) {
                                    if ($dataProduct['special_from_date']) {
                                        $fromDate = new DateTime($dataProduct['special_from_date']);
                                        if ($fromDate <= $today) {
                                            if ($dataProduct['special_to_date']) {
                                                $toDate = new DateTime($dataProduct['special_to_date']);
                                                if ($toDate >= $today) {
                                                    $postData['price'] = $dataProduct['special_price'];
                                                } else {
                                                    $postData['price'] = $dataProduct['price'];
                                                }
                                            } else {
                                                $postData['price'] = $dataProduct['special_price'];
                                            }
                                        } else {
                                            $postData['price'] = $dataProduct['price'];
                                        }
                                    } else {
                                        if ($dataProduct['special_to_date']) {
                                            $toDate = new DateTime($dataProduct['special_to_date']);
                                            if ($toDate >= $today) {
                                                $postData['price'] = $dataProduct['special_price'];
                                            } else {
                                                $postData['price'] = $dataProduct['price'];
                                            }
                                        } else {
                                            $postData['price'] = $dataProduct['special_price'];
                                        }
                                    }
                                } else {
                                    $postData['price'] = $dataProduct['price'];
                                }
                            } else {
                                $postData['price'] = $dataProduct['price'];
                            }
                        }

                        $params = array(
                            'description' => $newDescription,
                            'materials' => $item['materials'],
                            //'price'=>              $postData['price'],
                            'shipping_template_id' => $item['shipping_template_id'],
                            'shop_section_id' => $item['shop_section_id'],
                            'title' => $item['title'],
                            'tags' => $item['tags'],
                            'taxonomy_id' => $taxonomyID,
                            'who_made' => $item['who_made'],
                            'is_supply' => $dataSuppley,
                            'when_made' => $item['when_made'],
                            'recipient' => $item['recipient'],
                            'occasion' => $item['occasion'],
                            'style' => $styleData,
                            'should_auto_renew' => $renewalOption,
                            'language' => $item['language']);
                    }
                }

                if ($qty == 0) {
                    $params['state'] = Merchante_MagetSync_Model_Listing::STATE_INACTIVE;
                    $params['quantity'] = 1;
                } else {
                    if ($qty > 999) {
                        $params['quantity'] = 999;
                        $qty = 999;
                    } else {
                        $params['quantity'] = $qty;
                    }
                }
        /// getting custom title field flag from the configuration
        $isCustomTitle = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_change_product_title_attribute');
        if($isCustomTitle){
            $isCustomTitleAttribute = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_change_product_title_code');
            // getting custom attribute code for title
            if(!empty($isCustomTitleAttribute)){
                $customTitle = $product->getData($isCustomTitleAttribute);
                if(!empty($customTitle)){
                    $params['title'] = ucfirst($customTitle);
                }
            }
                        
        }
                    
        /// getting custom description field flag from the configuration
        $isCustomDescription = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_change_product_description_attribute');
                    
        if($isCustomDescription){
            $isCustomDescriptionAttribute = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_change_product_description_attribute_code');
            // getting custom attribute code for title
            if(!empty($isCustomDescriptionAttribute)){
                $customDesc = $product->getData($isCustomDescriptionAttribute);
                            
                if(!empty($customDesc)){
                    $textNoHtml = strip_tags($customDesc,'<br></br><br/><br />');
                    $newDescription = preg_replace('/(<br>)|(<\/br>)|(<br\/>)|(<br \/>)/',PHP_EOL,$textNoHtml);
                    $params['description'] = $newDescription;
                                
                }
            }
        }
                $obliUpd = array('listing_id' => $item['listing_id']);
                $resultApiUpd = $listingModel->updateListing($obliUpd, $params);
                if ($resultApiUpd['status'] == true) {
                    if ($autoSync == '1') {
                        if ($item['sync'] == Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC) {
                            $result = json_decode(json_decode($resultApiUpd['result']), true);
                            $result = $result['results'][0];
                            $statusProcess = $listingModel->saveDetails($result, $item['idproduct'], $item['price'], $item['id']);
                            if ($statusProcess['status']) {
                                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
                                $logData = Mage::getModel('magetsync/logData');
                                $logData->remove($item['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING);
                            } else {
                                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                                if ($statusProcess['message']) {
                                    $errorMessage = $statusProcess['message'];
                                    if (strpos($statusProcess['message'], 'The listing is not editable, must be active or expired but is removed') !== false) {
                                        $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
                                    }
                                } else {
                                    $errorMessage = Mage::helper('magetsync')->__('Error processing details');
                                }

                                Merchante_MagetSync_Model_LogData::magetsync($item['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                    $errorMessage, Merchante_MagetSync_Model_LogData::LEVEL_ERROR);

                                Mage::log("Error: " . print_r($errorMessage, true), null, 'magetsync_inventory.log');
                            }
                        }
                    }
                    $postData['quantity'] = $qty;
                    $postData['quantity_has_changed'] = Merchante_MagetSync_Model_Listing::QUANTITY_HAS_NOT_CHANGED;
                    //$postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
                } else {
                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                    Merchante_MagetSync_Model_LogData::magetsync($item['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                        $resultApiUpd['message'], Merchante_MagetSync_Model_LogData::LEVEL_ERROR);
                    if (strpos($resultApiUpd['message'], 'The listing is not editable, must be active or expired but is removed') !== false) {
                        $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
                    }

                }
                $listingModel
                    ->addData($postData)
                    ->setId($item['id']);
                $listingModel->save();
            }
        }
    }

    /// finction to reset images
    public function imagesResetEtsy($listing){
        if(!$listing){
            return false;
        }
        $idListing = $listing->getListingId();
        $result = array("listing_id" => $idListing);
        $idProduct = $listing->getIdproduct();
        $productModel = Mage::getModel('catalog/product')->load($idProduct);
        $dataPro = $productModel->getData();
        $newImages = array();
        // deleting images
        $h = 0;
        if (count($dataPro['media_gallery']['images']) > 0) {
            $excluded = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_exclude_pictures');
            if ($excluded <> '1') {
                foreach ($dataPro['media_gallery']['images'] as $imageAux) {
                    if ($imageAux['disabled'] != '1' && $imageAux['disabled_default'] != '1') {
                        $newImages[] = $imageAux;
                        if ($result['listing_id']) {
                            $imageModel = Mage::getModel('magetsync/imageEtsy')->getCollection();
                            $queryVerify = $imageModel->getSelect()->where('file = ?', $imageAux['file']);
                            $queryVerify = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryVerify);
                            if($queryVerify) {
                                $obligatoryDelete = array('listing_id' => $result['listing_id'], 'listing_image_id' => intval($queryVerify[0]['listing_image_id']));
                                $resultImageApiDelete = Mage::getModel('magetsync/listing')->deleteListingImage($obligatoryDelete, null);
                                if($resultImageApiDelete['status']) {
                                    $resultDeleteVerify = Mage::getModel('magetsync/imageEtsy')->setId($queryVerify[0]['id'])->delete();
                                }
                                else{
                                    Merchante_MagetSync_Model_LogData::magetsync($idListing,Merchante_MagetSync_Model_LogData::TYPE_LISTING, $resultImageApiDelete['message'],Merchante_MagetSync_Model_LogData::LEVEL_WARNING);
                                }
                            }
                        }
                    }
                }
                //
                //$newImages = $dataPro['media_gallery']['images'];
            } //end of excluded if
            //We sort and cut the array of images
            
            $imageUrl = $productModel->getImage();
            $resultIndex = $listing->searchForFile($imageUrl, $newImages);
            if(isset($resultIndex)) {
                $valueDelete = $newImages[$resultIndex];
                unset($newImages[$resultIndex]);
                //arsort($newImages);
                usort($newImages, function($a, $b) {
                    return strcmp($a->position, $b->position);
                });
                if(count($newImages) >= 5) {
                    $newImages = array_slice($newImages,0,4);
                }
                array_push($newImages, $valueDelete);
            }
        }

        // end of the count if condition
        try{
            $paramImg        = array('listing_id' => $result['listing_id']);
            $resultTotalImgs = Mage::getModel('magetsync/listing')->findAllListingImages($paramImg);
            $totalImages = 0;
            if($resultTotalImgs['status']) {
                $resultTotalImgs = json_decode(json_decode($resultTotalImgs['result']), true);
                $totalImagesAux = $resultTotalImgs['count'];
                $totalImages = isset($totalImagesAux) ? $totalImagesAux : 0;
            }else{
               Merchante_MagetSync_Model_LogData::magetsync($idListing,Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                    $resultTotalImgs['message'],Merchante_MagetSync_Model_LogData::LEVEL_WARNING);
            }
            foreach ($newImages as $image) {
                //We control that the number of images always
                //be 5 or less (Etsy restriction)
                if ($h < (5 - $totalImages)) {
                    $imageModel   = Mage::getModel('magetsync/imageEtsy')->getCollection();
                    $query        = $imageModel->getSelect()->where('file = ?', $image['file']);
                    $query        = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                    $file         = Mage::getBaseDir('media') . '/catalog/product' . $image['file'];
                    $info         = pathinfo($file);
                    $ext          = $info['extension'];
                    $mime         = Mage::getModel('magetsync/listing')->mimetypes[$ext];
                    $obligatory   = array('listing_id' => $result['listing_id']);
                    $etsyModel    = Mage::getModel('magetsync/etsy');
                    $url          = Merchante_MagetSync_Model_Etsy::$merchApi . 'Listing/saveImageUpload';
                    //According to the PHP_VERSION we use file_contents
                    //in different ways
                    if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                        $tempImage = curl_file_create($file,$mime,'tempImage');
                        $post = array('file_contents' => $tempImage);
                    }
                    else {
                        $post = array('file_contents' => '@' . $file);
                    }
                    $resultUpload = $etsyModel->curlConnect($url, $post, 2);
                    $resultUpload = json_decode($resultUpload, true);
                    if ($resultUpload['success'] == 1 || $resultUpload['success'] == true) {
                        $file = $resultUpload['upload'];
                        if ($query == null) {
                            $params = array('@image' => '@'.$file. ';type=' . $mime, 'name' => $file);
                        }else {
                            $params = array('@image' => '@'.$file. ';type=' . $mime, 'listing_image_id' => intval($query[0]['listing_image_id']), 'name' => $file);
                            $obligatoryDelete = array('listing_id' => $result['listing_id'], 'listing_image_id' => intval($query[0]['listing_image_id']));
                            $resultImageApiDelete = Mage::getModel('magetsync/listing')->deleteListingImage($obligatoryDelete, null);
                            if(!$resultImageApiDelete['status']) {
                               Merchante_MagetSync_Model_LogData::magetsync($idListing,Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                    $resultImageApiDelete['message'],Merchante_MagetSync_Model_LogData::LEVEL_WARNING);
                            }
                        }

                        $resultImageApi = Mage::getModel('magetsync/listing')->uploadListingImage($obligatory, $params);

                        if ($resultImageApi['status']) {
                            $resultImage = json_decode(json_decode($resultImageApi['result']), true);
                            $resultImage = $resultImage['results'][0];
                            $imageData = array('listing_id' => $resultImage['listing_id'], 'listing_image_id' => $resultImage['listing_image_id'], 'file' => $image['file']);
                            if ($query[0]['id']) {
                                $resultSaveImage = Mage::getModel('magetsync/imageEtsy')->load($query[0]['id'])
                                    ->addData($imageData)
                                    ->setId($query[0]['id']);
                                $resultSaveImage->save();
                            }else {
                                $imageEtsyModel = Mage::getModel('magetsync/imageEtsy');
                                $imageEtsyModel->setData($imageData);
                                $imageEtsyModel->save();
                            }

                        }else {
                            throw new Exception($resultImageApi['message']);
                        }

                    }else{
                        Merchante_MagetSync_Model_LogData::magetsync($idListing,Merchante_MagetSync_Model_LogData::TYPE_LISTING, $resultUpload['message'],Merchante_MagetSync_Model_LogData::LEVEL_ERROR);
                    }
                    $h = $h + 1;
                }
            }

        }catch (Exception $e){
            return array('status'=>false,'message'=>$e->getMessage());
        }
    }



    public function sendAutoQueue()
    {
        $etsyConfigVerified = Mage::getModel('magetsync/etsy')->verifyDataConfiguration();
        if ($etsyConfigVerified) {
            $dataGlobal = '';
            $iterationCntr = 0;
            try {
                $listingModel = Mage::getModel('magetsync/listing');
                $listings = $listingModel->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('sync', array('eq' => Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE))
                    ->load();
                foreach ($listings as $listing) {
                    if ($iterationCntr > $this::AUTOQUEUE_ITERATIONS_LIMIT) {
                        break;
                    }
                    $data = $listing->getData();
                    // checking if the product is already there in the list synchronizing only images
                    if ($data['listing_id']) {
                       $this->imagesResetEtsy($listing);
                    }
                    else{ // loop to add new listing and sync the product
                        $new_pricing = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_enable_different_pricing');
                        $listingProduct = Mage::getModel('catalog/product')->load($data['idproduct']);
                        if ($new_pricing) {
                            $price = $data['price'];
                        } else {
                            $price = round($listingProduct->getPrice(), 2);
                        }

                        $qty = round($listingProduct->getStockItem()->getQty(), 2);

                        $supply = isset($data['is_supply']) ? $data['is_supply'] : 1;
                        if ($supply == 1) {
                            $supply = 0;
                        } else {
                            $supply = 1;
                        }

                        $defaultStoreId = Mage::app()
                            ->getWebsite(true)
                            ->getDefaultGroup()
                            ->getDefaultStoreId();
                        $language = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_language', $defaultStoreId);
                        if (empty($language)) {
                            throw new Exception(Mage::helper('magetsync')->__('Must configure Etsy\'s language'));
                            continue;
                        }

                        $stateListing = Merchante_MagetSync_Model_Listing::STATE_ACTIVE;
                        if ($qty == 0) {
                            $stateListing = Merchante_MagetSync_Model_Listing::STATE_INACTIVE;
                            // To pass Etsy API restrictions
                            $qty = 1;
                        }

                        $taxonomyID = $listingModel->getTaxonomyID($data);

                        $params = array(
                            'description' => !empty($data['description']) ? $data['description'] : '',
                            'materials' => !empty($data['materials']) ? $data['materials'] : '',
                            'state' => $stateListing,
                            'quantity' => $qty,
                            'price' => $price,
                            'shipping_template_id' => !empty($data['shipping_template_id']) ? $data['shipping_template_id'] : '',
                            'shop_section_id' => !empty($data['shop_section_id']) ? $data['shop_section_id'] : '',
                            'title' => !empty($data['title']) ? $data['title'] : '',
                            'tags' => !empty($data['tags']) ? $data['tags'] : '',
                            'taxonomy_id' => $taxonomyID,
                            'who_made' => !empty($data['who_made']) ? $data['who_made'] : '',
                            'is_supply' => $supply,
                            'when_made' => !empty($data['when_made']) ? $data['when_made'] : '',
                            'recipient' => !empty($data['recipient']) ? $data['recipient'] : '',
                            'occasion' => !empty($data['occasion']) ? $data['occasion'] : '',
                            'style' => !empty($data['style']) ? $data['style'] : '',
                            'should_auto_renew' => !empty($data['should_auto_renew']) ? $data['should_auto_renew'] : 0,
                            'language' => $language
                            );
                        $dataGlobal = $data['id'];
                        $hasError = false;

                        $resultApi = $listingModel->createListing(null, $params);
                       
                        if ($resultApi['status'] == true) {

                            $result = json_decode(json_decode($resultApi['result']), true);
                            $result = $result['results'][0];
                            $statusOperation = $listingModel->saveDetails($result, $data['idproduct'], $params['price'], $dataGlobal);

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

                            if ($statusOperation['status']) {
                                if ($result['state'] == 'edit') {
                                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
                                } else {
                                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
                                }
                            } else {
                                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                                $hasError = true;
                                if ($statusOperation['message']) {
                                    $resultApi['message'] = $statusOperation['message'];
                                } else {
                                    $resultApi['message'] = Mage::helper('magetsync')->__('Error processing details');
                                }
                            }
                        } else {
                            $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                            $hasError = true;
                            if (strpos($resultApi['message'], 'The listing is not editable, must be active or expired but is removed') !== false) {
                                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
                            }
                        }

                        $listing->addData($postData);
                        $listing->save();

                        $iterationCntr++;

                        if ($hasError == true) {
                            Merchante_MagetSync_Model_LogData::magetsync($dataGlobal,
                                Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                $resultApi['message'],
                                Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                            );
                        } else {
                            // Clean logs
                            $logData = Mage::getModel('magetsync/logData');
                            $logData->remove($dataGlobal, Merchante_MagetSync_Model_LogData::TYPE_LISTING);

                        }



                    }
                }
            } catch (Exception $e) {
                if ($e instanceof OAuthException) {
                    $errorMsg = $e->lastResponse;
                } else {
                    $errorMsg = $e->getMessage();
                }
                Mage::log("Error: " . print_r($errorMsg, true), null, 'magetsync_listing.log');

                if ($dataGlobal) {
                    Merchante_MagetSync_Model_LogData::magetsync(
                        $dataGlobal,
                        Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                        $errorMsg,
                        Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                    );

                    $listingModel = Mage::getModel('magetsync/listing')->load($dataGlobal);
                    $listingModel->addData(array('sync' => null))
                        ->setId($dataGlobal)
                        ->save();
                }
            }
        }
    }


    public function importUpdateQuantity($observer) {
        $adapter = $observer->getEvent()->getAdapter();
        $ids = $adapter->getAffectedEntityIds();
        foreach($ids as $product_id){
            $product = Mage::getModel('catalog/product')->load($product_id);
            $data = $product->getData();
            if($data['visibility'] != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE){
                $status = (isset($data['synchronizedEtsy'])?$data['synchronizedEtsy']:null);
                $parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($data['entity_id']);
                if(!$parent) {
                    if ($status == 1) {
                        $listingModel = Mage::getModel('magetsync/listing');
                        $query = $listingModel->getCollection()->getSelect()->where('idproduct = ?', $data['entity_id']);
                        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                        $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($product)->getQty();
                        $dataSave = array("idproduct" => $data['entity_id'], "quantity" => $stocklevel,
                        "quantity_has_changed" => Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED);
                        if (!empty($query) && $query[0]['quantity'] != $stocklevel) {
                            $listingModel->addData($dataSave)->setId($query[0]['id']);
                            $listingModel->save();
                        }
                    }
                }
            }
        }
    }
    
    public function updateProductQty($observer){
        $creditMemo = $observer->getEvent()->getCreditmemo();
        foreach ($creditMemo->getAllItems() as $item) {
            $productId = $item->getProductId();
            $listing = Mage::getModel('magetsync/listing')->load($productId,'idproduct');
            $totalQty = '';
            if($listing->getId()){
                $id = $listing->getId();
                $refundQty = $item->getQty();
                $totalQty = $listing->getQuantity() + $refundQty;
                $listing->setQuantity($totalQty);
                $dataSave = array("quantity_has_changed" => Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED,
                "sync" => Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC);
                $listing->addData($dataSave)->setId($id);
                $listing->save();
            }
        }
    }
}