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
                Mage::log("Error: " . print_r($dataApi['message'], true), null, 'track.log');
            }
        }
        return;
        } catch (Exception $e){
            Mage::log("Error: ".print_r($e, true),null,'track.log');
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
            Mage::log("Error: " . print_r($e->getMessage(), true), null, 'order.log');
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

                                Mage::log("Error: " . print_r($errorMessage, true), null, 'inventory.log');
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
}