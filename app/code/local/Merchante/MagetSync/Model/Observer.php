<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Observer
 */
class Merchante_MagetSync_Model_Observer
{
    /**
     * Number of products sent to Etsy per cron job
     */
    const AUTOQUEUE_ITERATIONS_LIMIT = 3;

    /**
     * @event sales_order_shipment_track_save_before
     * @param $observer
     */
    public function saveTrack($observer)
    {
        try {
            $info = $observer->getData();
            $info = $info['track'];
            $carrier = $info->getData();
            $carrier = $carrier['carrier_code'];
            $track_number = $info->getData();
            $track_number = $track_number['track_number'];
            $orderId = $observer->getEvent()->getTrack()->getOrderId();

            if ($carrier == 'australiapost') {
                $carrier = 'australia-post';
            }

            $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');

            /** @var Merchante_MagetSync_Model_Receipt $receipt */
            $receipt = Mage::getModel('magetsync/receipt');

            /** @var Merchante_MagetSync_Model_Mysql4_OrderEtsy_Collection $orderECollection */
            $orderECollection = Mage::getModel('magetsync/orderEtsy')->getCollection();

            $query = $orderECollection->getSelect()->where('order_id = ?', $orderId);
            $query = $this->getReadAdapter()->fetchAll($query);

            if ($query) {
                $obligatory = array(
                    'shop_id'    => $shop,
                    'receipt_id' => $query[0]['receipt_id']
                );
                $params = array(
                    'tracking_code' => $track_number,
                    'carrier_name'  => $carrier
                );
                $dataApi = $receipt->submitTracking($obligatory, $params);
                if ($dataApi['status'] == true) {

                } else {
                    Mage::log("Error: " . print_r($dataApi['message'], true), null, 'magetsync_track.log');
                }
            }

            return;
        } catch (Exception $e) {
            Mage::log('Error: ' . print_r($e, true), null, 'magetsync_track.log');

            return;
        }
    }

    /**
     * Set option array
     *
     * @param $values
     * @param $arrOptions
     * @param $optionID
     * @param string $option_type_id
     * @return mixed
     */
    public function setOptionArray($values, $arrOptions, $optionID, $option_type_id = 'option_type_id')
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
     * Create order
     *
     * @return boolean
     * @cronjob magetsync
     */
    public function createOrder()
    {
        try {
            /** @var Merchante_MagetSync_Model_Order $orderModel */
            $orderModel = Mage::getModel('magetsync/order');

            return $orderModel->makeOrder();
        } catch (Exception $e) {
            Mage::log("Error: " . print_r($e->getMessage(), true), null, 'magetsync_order.log');

            return false;
        }
    }

    /**
     * Check listings expire
     *
     * @cronjob magetsync_checklistingsexpired
     */
    public function checkListingsExpired()
    {

        if (!$this->isEtsyConfigVerified()) {
            return;
        }

        $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');

        /** @var Merchante_MagetSync_Model_Listing $listingModel */
        $listingModel = Mage::getModel('magetsync/listing');


        $query = $listingModel->getCollection()->getSelect()->where(
            '(sync =' . Merchante_MagetSync_Model_Listing::STATE_SYNCED .
            ' OR sync =' . Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC
            . ') AND enabled =' . Merchante_MagetSync_Model_Listing::LISTING_ENABLED
        );

        /** @var [] $results */
        $results = $this->getReadAdapter()->fetchAll($query);

        /** @var [] $item */
        foreach ($results as $item) {

            if (!$shop) {
                continue;
            }

            $obliUpd = array(
                'listing_id' => $item['listing_id'],
                'shop_id'    => $shop
            );

            $resultApiGet = $listingModel->getShopListingExpired($obliUpd, null);

            if ($resultApiGet['status'] == true) {
                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
                $listingModel
                    ->addData($postData)
                    ->setId($item['id']);
                $listingModel->save();
            } else {
                Merchante_MagetSync_Model_LogData::magetsync(
                    $item['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                    $resultApiGet['message'], Merchante_MagetSync_Model_LogData::LEVEL_WARNING
                );
            }

        }


    }

    /**
     * Check product inventory
     *
     * @cronjob magetsync_inventory
     */
    public function checkInventory()
    {

        if (!$this->isEtsyConfigVerified()) {
            return;
        }

        $autoSync = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_auto_sync');

        $listingModel = Mage::getModel('magetsync/listing');

        $query = $listingModel->getCollection()->getSelect()->where(
            '(quantity_has_changed =' . Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED .
            ' OR sync =' . Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC
            . ') AND enabled =' . Merchante_MagetSync_Model_Listing::LISTING_ENABLED . ' AND sync !=' .
            Merchante_MagetSync_Model_Listing::STATE_INQUEUE . ' AND sync !='
            . Merchante_MagetSync_Model_Listing::STATE_FAILED . ' AND sync !=' .
            Merchante_MagetSync_Model_Listing::STATE_EXPIRED .
            ' AND sync !=' . Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE
        );

        $listings = $this->getReadAdapter()->fetchAll($query);

        /** @var [] $listing */
        foreach ($listings as $listing) {
            $product = Mage::getModel('catalog/product')->load($listing['idproduct']);
            $dataProduct = $product->getData();
            $qty = 0;
            $params = array();
            $postData = array();
            $skipVariationInventoryUpdate = false;

            if ($product['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
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
                $skipVariationInventoryUpdate = true;
            }

            if ($autoSync == '1') {
                if ($listing['sync'] == Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC) {

                    $taxonomyID = $listingModel->getTaxonomyID($listing, null);

                    $newDescription = $listingModel->composeDescription(
                        $listing['description'],
                        $listing['prepended_template'],
                        $listing['appended_template'],
                        $listing['idproduct']
                    );

                    $supply = isset($listing['is_supply']) ? $listing['is_supply'] : 1;
                    //Boolean field in Etsy but not 'Yes/No' in frontEnd
                    if ($supply == 1) {
                        $dataSuppley = 0;
                    } else {
                        $dataSuppley = 1;
                    }
                    $renewalOption = (isset($listing['should_auto_renew']) &&
                        $listing['should_auto_renew']) ? $listing['should_auto_renew'] : 0;

                    if (isset($listing['tags'])) {
                        $search = array(
                            ';',
                            '.',
                            '/',
                            '\\'
                        );
                        $listing['tags'] = str_replace($search, '', $listing['tags']);
                        $newTagsAux = explode(',', strtolower($listing['tags']));
                        $newTags = array_unique($newTagsAux);
                        $listing['tags'] = implode(',', $newTags);
                    }

                    $postData['price'] = $dataProduct['price'];
                    if ($listing['is_custom_price'] == 1) {
                        $postData['price'] = $listing['price'];
                    } else {
                        if ($dataProduct['special_price'] != '') {
                            $useSpecialPrice = Mage::getStoreConfig(
                                'magetsync_section/magetsync_group_options/magetsync_field_special_price'
                            );
                            if ($useSpecialPrice) {
                                $today = new DateTime("now");
                                if ($dataProduct['special_from_date']) {
                                    $fromDate = new DateTime($dataProduct['special_from_date']);
                                    if ($fromDate <= $today) {
                                        if ($dataProduct['special_to_date']) {
                                            $toDate = new DateTime($dataProduct['special_to_date']);
                                            if ($toDate >= $today) {
                                                $postData['price'] = $dataProduct['special_price'];
                                            }
                                        } else {
                                            $postData['price'] = $dataProduct['special_price'];
                                        }
                                    }
                                } else {
                                    if ($dataProduct['special_to_date']) {
                                        $toDate = new DateTime($dataProduct['special_to_date']);
                                        if ($toDate >= $today) {
                                            $postData['price'] = $dataProduct['special_price'];
                                        }
                                    } else {
                                        $postData['price'] = $dataProduct['special_price'];
                                    }
                                }
                            }
                        }
                    }

                    $params = array(
                        'description'          => $newDescription,
                        'materials'            => $listing['materials'],
                        'shipping_template_id' => $listing['shipping_template_id'],
                        'shop_section_id'      => $listing['shop_section_id'],
                        'title'                => $listing['title'],
                        'tags'                 => $listing['tags'],
                        'taxonomy_id'          => $taxonomyID,
                        'who_made'             => $listing['who_made'],
                        'is_supply'            => $dataSuppley,
                        'when_made'            => $listing['when_made'],
                        'should_auto_renew'    => $renewalOption,
                        'language'             => $listing['language']
                    );
                }
            }

            if (!$skipVariationInventoryUpdate) {
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
                }}
            /// getting custom title field flag from the configuration
            $isCustomTitle = Mage::getStoreConfig(
                'magetsync_section/magetsync_group_options/magetsync_field_change_product_title_attribute'
            );
            if ($isCustomTitle) {
                $isCustomTitleAttribute = Mage::getStoreConfig(
                    'magetsync_section/magetsync_group_options/magetsync_field_change_product_title_code'
                );
                // getting custom attribute code for title
                if (!empty($isCustomTitleAttribute)) {
                    $customTitle = $product->getData($isCustomTitleAttribute);
                    if (!empty($customTitle)) {
                        $params['title'] = ucfirst($customTitle);
                    }
                }

            }

            /// getting custom description field flag from the configuration
            $isCustomDescription = Mage::getStoreConfig(
                'magetsync_section/magetsync_group_options/magetsync_field_change_product_description_attribute'
            );

            if ($isCustomDescription) {
                $isCustomDescriptionAttribute = Mage::getStoreConfig(
                    'magetsync_section/magetsync_group_options/magetsync_field_change_product_description_attribute_code'
                );
                // getting custom attribute code for title
                if (!empty($isCustomDescriptionAttribute)) {
                    $customDesc = $product->getData($isCustomDescriptionAttribute);

                    if (!empty($customDesc)) {
                        $textNoHtml = strip_tags($customDesc, '<br></br><br/><br />');
                        $newDescription =
                            preg_replace('/(<br>)|(<\/br>)|(<br\/>)|(<br \/>)/', PHP_EOL, $textNoHtml);
                        $params['description'] = $newDescription;

                    }
                }
            }
            $obliUpd = array('listing_id' => $listing['listing_id']);

            $resultApiUpd = $listingModel->updateListing($obliUpd, $params);
            if ($resultApiUpd['status'] == true) {

                //Update custom Listing attributes
                $propertiesArr = $listing->getProperties();
                if ($propertiesArr) {
                    foreach ($propertiesArr as $propertyKey => $propertyVal) {
                        $obliUpd['property_id'] = $propertyKey;
                        $attributeUpdateParams = array();
                        if (is_array($propertyVal)) {
                            $propertyVal = implode(',', $propertyVal);
                        }
                        $attributeUpdateParams['value_ids'] = $propertyVal;
                        $updateAttributeApi = $listing->updateAttribute($obliUpd, $attributeUpdateParams);
                        if ($updateAttributeApi['status'] != true) {
                            Mage::getSingleton('adminhtml/session')->addError('Unable to update one of custom attributes.');
                            Merchante_MagetSync_Model_LogData::magetsync(
                                $postData, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                $updateAttributeApi['message'], Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                            );
                        }
                    }
                }
                if ($autoSync == '1') {
                    if ($listing['sync'] == Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC) {
                        $result = json_decode(json_decode($resultApiUpd['result']), true);
                        $result = $result['results'][0];
                        $callType = 'inventory';
                        $statusProcess = $listingModel->saveDetails(
                            $result, $listing['idproduct'], $listing['price'], $listing['id'], $callType
                        );

                        if ($statusProcess['status']) {
                            $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
                            $logData = Mage::getModel('magetsync/logData');
                            $logData->remove($listing['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING);
                        } else {
                            $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                            if ($statusProcess['message']) {
                                $errorMessage = $statusProcess['message'];
                                if (strpos(
                                        $statusProcess['message'],
                                        'The listing is not editable, must be active or expired but is removed'
                                    ) !== false
                                ) {
                                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
                                }
                            } else {
                                $errorMessage = Mage::helper('magetsync')->__('Error processing details');
                            }

                            Merchante_MagetSync_Model_LogData::magetsync(
                                $listing['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                $errorMessage, Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                            );

                            Mage::log("Error: " . print_r($errorMessage, true), null, 'magetsync_inventory.log');
                        }
                    }
                }
                $postData['quantity'] = $qty;
                $postData['quantity_has_changed'] = Merchante_MagetSync_Model_Listing::QUANTITY_HAS_NOT_CHANGED;
                //$postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
            } else {
                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                Merchante_MagetSync_Model_LogData::magetsync(
                    $listing['id'], Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                    $resultApiUpd['message'], Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                );
                if (strpos(
                        $resultApiUpd['message'],
                        'The listing is not editable, must be active or expired but is removed'
                    ) !== false
                ) {
                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
                }

            }
            $listingModel
                ->addData($postData)
                ->setId($listing['id']);
            $listingModel->save();
        }

    }


    /**
     * function to reset images
     *
     * @param $listing
     * @return bool
     * @throws Exception
     */
    public function imagesResetEtsy($listing)
    {
        if (!$listing) {
            return false;
        }

        $listing->setSync(Merchante_MagetSync_Model_Listing::STATE_SYNCED);
        $idListing = $listing->getListingId();

        if (!$idListing) {
            return false;
        }

        $result = array("listing_id" => $idListing);
        $idProduct = $listing->getIdproduct();

        $productModel = Mage::getModel('catalog/product')->load($idProduct);

        if (!is_numeric($productModel->getId())) {
            return false;
        }

        $dataPro = $productModel->getData();

        $newImages = array();

        $imageModel = Mage::getModel('magetsync/imageEtsy')
            ->getCollection()
            ->addFieldToFilter('listing_id', array('eq' => $idListing));

        /** @var Merchante_MagetSync_Model_ImageEtsy $img */
        foreach ($imageModel as $img) {
            $img->delete();
        }

        // deleting images
        if (count($dataPro['media_gallery']['images']) > 0) {
            $uploadExcluded = Mage::getStoreConfig('magetsync_section/magetsync_group_options/magetsync_field_exclude_pictures');
            foreach ($dataPro['media_gallery']['images'] as $imageAux) {
                if (($imageAux['disabled'] != '1' && $imageAux['disabled_default'] != '1') || $uploadExcluded == 1) {
                    if ($productModel->getImage() == $imageAux['file']) {
                        $baseImage = $imageAux;
                    } else {
                        $newImages[] = $imageAux;
                    }
                }
            }

            //We sort and cut the array of images when no image is there to sync
            if (empty($newImages)) {
                $listing->setSync(Merchante_MagetSync_Model_Listing::STATE_SYNCED);
                $listing->save();

                return false;
            }
            usort(
                $newImages, function ($a, $b) {
                return ($a['position'] > $b['position']) ? 1 : -1;
            }
            );
            // Insert base image as first
            if (!empty($baseImage)) {
                array_unshift($newImages, $baseImage);
            }
            if (count($newImages) > 5) {
                $newImages = array_slice($newImages, 0, 5);
            }

        }

        $newImages = array_reverse($newImages);
        $paramImg = array('listing_id' => $result['listing_id']);
        // deleting all the images on etsy
        $resultTotalImgs = Mage::getModel('magetsync/listing')->findAllListingImages($paramImg);

        if ($resultTotalImgs['status']) {
            $resultTotalImgs = json_decode(json_decode($resultTotalImgs['result']), true);
            if ($result['listing_id'] && isset($resultTotalImgs['results']) && count($resultTotalImgs['results']) > 0) {
                foreach ($resultTotalImgs['results'] as $etsyImg) {
                    $obligatoryDelete = array(
                        'listing_id'       => $result['listing_id'],
                        'listing_image_id' => intval($etsyImg['listing_image_id'])
                    );
                    $resultImageApiDelete =
                        Mage::getModel('magetsync/listing')->deleteListingImage($obligatoryDelete, null);
                    if (!$resultImageApiDelete['status']) {
                        Merchante_MagetSync_Model_LogData::magetsync(
                            $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                            $resultImageApiDelete['message'], Merchante_MagetSync_Model_LogData::LEVEL_WARNING
                        );
                    }
                }
            }
        }

        $resultTotalImgs = Mage::getModel('magetsync/listing')->findAllListingImages($paramImg);
        $totalImages = 0;
        if ($resultTotalImgs['status']) {
            $resultTotalImgs = json_decode(json_decode($resultTotalImgs['result']), true);
            $totalImagesAux = $resultTotalImgs['count'];
            $totalImages = isset($totalImagesAux) ? $totalImagesAux : 0;
        } else {
            Merchante_MagetSync_Model_LogData::magetsync(
                $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                $resultTotalImgs['message'], Merchante_MagetSync_Model_LogData::LEVEL_WARNING
            );
        }
        $h = 0;
        $uploadCount = 0;

        $imageModel = Mage::getModel('magetsync/imageEtsy')->getCollection();

        foreach ($newImages as $image) {
            //We control that the number of images always
            //be 5 or less (Etsy restriction)
            if ($h < (5 - $totalImages)) {

                $imgInfo = pathinfo($imageAux['file']);

                $query = $imageModel->getSelect()->where('file = ?', $image['file'])->orwhere(
                    'file = ?', $imgInfo['dirname'] . DS . 'etsy_' . $imgInfo['basename']
                );
                $query = $this->getReadAdapter()->fetchAll($query);

                $file = Mage::getBaseDir('media') . '/catalog/product' . $image['file'];
                $info = pathinfo($file);

                $resizePathFull = $info['dirname'] . DS . "etsy_" . $info['basename'];
                if (file_exists($resizePathFull)) {
                    $file = $resizePathFull;
                    $info = pathinfo($file);
                }

                $ext = $info['extension'];
                $mime = Mage::getModel('magetsync/listing')->mimetypes[$ext];

                $obligatory = array('listing_id' => $result['listing_id']);
                $etsyModel = Mage::getModel('magetsync/etsy');
                $url = Merchante_MagetSync_Model_Etsy::$merchApi . 'Listing/saveImageUpload';
                //According to the PHP_VERSION we use file_contents
                //in different ways
                if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                    $tempImage = curl_file_create($file, $mime, 'tempImage');
                    $post = array('file_contents' => $tempImage);
                } else {
                    $post = array('file_contents' => '@' . $file);
                }
                $resultUpload = $etsyModel->curlConnect($url, $post, 2);
                $resultUpload = json_decode($resultUpload, true);
                if ($resultUpload['success'] == 1 || $resultUpload['success'] == true) {
                    $file = $resultUpload['upload'];
                    $params = array(
                        '@image' => '@' . $file . ';type=' . $mime,
                        'name'   => $file
                    );

                    $resultImageApi = Mage::getModel('magetsync/listing')->uploadListingImage($obligatory, $params);
                    if ($resultImageApi['status']) {
                        $resultImage = json_decode(json_decode($resultImageApi['result']), true);
                        $resultImage = $resultImage['results'][0];
                        $imageData = array(
                            'listing_id'       => $resultImage['listing_id'],
                            'listing_image_id' => $resultImage['listing_image_id'],
                            'file'             => $image['file']
                        );
                        if ($query[0]['id']) {
                            $resultSaveImage = Mage::getModel('magetsync/imageEtsy')->load($query[0]['id'])
                                ->addData($imageData)
                                ->setId($query[0]['id']);
                            $resultSaveImage->save();
                        } else {
                            $imageEtsyModel = Mage::getModel('magetsync/imageEtsy');
                            $imageEtsyModel->setData($imageData);
                            $imageEtsyModel->save();
                        }
                        $uploadCount++;
                    } else {
                        Merchante_MagetSync_Model_LogData::magetsync(
                            $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING, $resultImageApi['message'],
                            Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                        );
                    }
                } else {
                    Merchante_MagetSync_Model_LogData::magetsync(
                        $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING, $resultUpload['message'],
                        Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                    );
                }
                $h = $h + 1;
            }
        }
        if ($uploadCount > 0) {
            $listing->setSync(Merchante_MagetSync_Model_Listing::STATE_SYNCED);
        } else {
            $listing->setSync(Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC);
        }
        $listing->save();
    }

    /**
     * Image reset
     *
     * @cronjob magetsync_imagereset
     */
    public function imageResetCron()
    {
        if (!$this->isEtsyConfigVerified()) {
            return;
        }

        $iterationCntr = 0;
        /** @var Merchante_MagetSync_Model_Listing $listingModel */
        $listingModel = Mage::getModel('magetsync/listing');

        $listings = $listingModel->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'sync', array('eq' => Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE)
            )
            ->load();
        /** @var Merchante_MagetSync_Model_Listing $listing */
        foreach ($listings as $listing) {
            if ($iterationCntr > 2) {
                break;
            }
            $data = $listing->getData();
            // checking if the product is already there in the list synchronizing only images
            if ($data['listing_id']) {
                $this->imagesResetEtsy($listing);
                $iterationCntr++;
            }
        }

    }


    /**
     * Send auto queue
     *
     * @cronjob magetsync_autolist
     */
    public function sendAutoQueue()
    {
        if (!$this->isEtsyConfigVerified()) {
            return;
        }

        /** @var Merchante_MagetSync_Model_Service_ListingService $listingService */
        $listingService = Mage::getModel('magetsync/service_listingService');
        $listingService->processListingCollectionApi();
    }


    /**
     * Import quantity update
     *
     * @param $observer
     * @throws Exception
     * @event catalog_product_import_finish_before
     */
    public function importUpdateQuantity($observer)
    {
        /** @var  $adapter */
        $adapter = $observer->getEvent()->getAdapter();

        $ids = $adapter->getAffectedEntityIds();

        foreach ($ids as $product_id) {
            $product = Mage::getModel('catalog/product')->load($product_id);
            $data = $product->getData();
            if ($data['visibility'] != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                $status = (isset($data['synchronizedEtsy']) ? $data['synchronizedEtsy'] : null);
                $parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($data['entity_id']);
                if (!$parent) {
                    if ($status == 1) {
                        $listingModel = Mage::getModel('magetsync/listing');
                        $query =
                            $listingModel->getCollection()->getSelect()->where('idproduct = ?', $data['entity_id']);
                        $query = $this->getReadAdapter()->fetchAll($query);
                        $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')
                            ->loadByProduct($product)->getQty();
                        $dataSave = array(
                            "idproduct"            => $data['entity_id'],
                            "quantity"             => $stocklevel,
                            "quantity_has_changed" => Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED
                        );
                        if (!empty($query) && $query[0]['quantity'] != $stocklevel) {
                            $listingModel->addData($dataSave)->setId($query[0]['id']);
                            $listingModel->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Update product quantity
     *
     * @param $observer
     * @throws Exception
     * @event sales_order_creditmemo_save_after
     */
    public function updateProductQty($observer)
    {
        /** @var  $creditMemo */
        $creditMemo = $observer->getEvent()->getCreditmemo();

        /** @var  $item */
        foreach ($creditMemo->getAllItems() as $item) {
            $productId = $item->getProductId();
            $listing = Mage::getModel('magetsync/listing')->load($productId, 'idproduct');
            $totalQty = '';
            if ($listing->getId()) {
                $id = $listing->getId();
                $refundQty = $item->getQty();
                $totalQty = $listing->getQuantity() + $refundQty;
                $listing->setQuantity($totalQty);
                $dataSave = array(
                    "quantity_has_changed" => Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED,
                    "sync"                 => Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC
                );
                $listing->addData($dataSave)->setId($id);
                $listing->save();
            }
        }
    }

    /**
     * Get read adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Verify Etsy configuration
     *
     * @return bool
     */
    protected function isEtsyConfigVerified()
    {
        /** @var Merchante_MagetSync_Model_Etsy $etsyModel */
        $etsyModel = Mage::getModel('magetsync/etsy');

        if ($etsyModel->verifyDataConfiguration()) {
            return true;
        }

        return false;
    }
}
