<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Listing
 */
class Merchante_MagetSync_Model_Listing extends Merchante_MagetSync_Model_Etsy
{
    /**
     *
     */
    const STATE_INQUEUE = 1;
    const STATE_SYNCED = 2;
    const STATE_FAILED = 3;
    const STATE_OUTOFSYNC = 4;
    const STATE_EXPIRED = 5;
    const STATE_MAPPED = 6;
    const STATE_FORCE_DELETE = 7;
    const STATE_AUTO_QUEUE = 8;

    const STATE_ACTIVE = 'active';
    const STATE_INACTIVE = 'inactive';
    const STATE_DRAFT = 'draft';

    const QUANTITY_HAS_CHANGED = 1;
    const QUANTITY_HAS_NOT_CHANGED = 0;

    const LISTING_ENABLED = 1;
    const LISTING_DISABLED = 0;

    /**
     * @var array
     */
    public $mimetypes = array(
        "png" => "image/png",
        "gif" => "image/gif",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg"
    );
    /**
     * @var string
     */
    public $name = 'Listing';

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/listing');
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllShopListingsActive($obligatory, $params = null)
    {

        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllShopListingsInactive($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllListingImages($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function createListing($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateListing($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getListing($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getShopListingExpired($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getShopListingInactive($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function deleteListing($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function uploadListingImage($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function deleteListingImage($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);

        return $result;
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function matchingListingsAux($offset)
    {
        try {
            $obligatory =
                array('shop_id' => Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop'));
            $params = array(
                'includes' => 'MainImage',
                'offset' => intval($offset),
                'limit' => 25
            );
            $listingsResult = $this->findAllShopListingsActive($obligatory, $params);
            if ($listingsResult['status']) {
                $resultAux = json_decode(json_decode($listingsResult['result']), true);
                $result = $resultAux['results'];
                $count = count($result);
                $changes = 0;
                $changesCount = 0;
                $mappingModel = Mage::getModel('magetsync/mappingEtsy');
                $listingModel = Mage::getModel('magetsync/listing');
                $productModel = Mage::getModel('catalog/product');
                $resource = Mage::getSingleton('core/resource');
                // generating regular expressions based on the SKU config
                $regularExpression = "";
                $skupattern = Mage::getStoreConfig(
                    'magetsync_section_draftmode/magetsync_group_mapping/magetsync_field_sku_pattern'
                );
                $regularExpression = "/";
                $a = 0;
                $n = 0;
                $strlen = strlen($skupattern);
                $strtoarray = str_split($skupattern, 1);
                foreach ($strtoarray as $key => $array) {
                    if (strtoupper($array) == "A") {
                        if ($n != 0) {
                            $regularExpression .= "[0-9]{1,$n}";
                            $n = 0;
                        }
                        $a++;
                    } elseif (strtoupper($array) == "N") {
                        if ($a != 0) {
                            $regularExpression .= "[a-zA-Z]{1,$a}";
                            $a = 0;
                        }
                        $n++;
                    }
                    if ($strlen == $key + 1) {
                        if ($a == 0 && $n != 0) {
                            $regularExpression .= "[0-9]{1,$n}";
                        } elseif ($n == 0 && $a != 0) {
                            $regularExpression .= "[a-zA-Z]{1,$a}";
                        }
                    }
                }
                $regularExpression .= "/";

                foreach ($result as $item) {
                    $queryM = $mappingModel->getCollection()->addFieldToSelect('etsy_id')->getSelect()->where(
                        'etsy_id = ?', $item['listing_id']
                    );
                    $queryM = $resource->getConnection('core_read')->fetchAll($queryM);
                    if (!$queryM) {
                        $query = $listingModel->getCollection()->addFieldToSelect('listing_id')->getSelect()->where(
                            'listing_id = ?', $item['listing_id']
                        );
                        $query = $resource->getConnection('core_read')->fetchAll($query);
                        if (!$query) {
                            $skuSearch = false;
                            // checking if the regular expression is set
                            if (!empty($regularExpression)) {
                                preg_match($regularExpression, $item['title'], $sku_array);
                                if (!empty($sku_array)) { // if any match found
                                    $productsCollection = $productModel->getCollection()
                                        ->addAttributeToSelect('name')
                                        ->addAttributeToSelect('sku')
                                        ->addAttributeToSelect('entity_id')
                                        ->addAttributeToFilter(
                                            array(
                                                array(
                                                    'attribute' => 'name',
                                                    'like' => $item['title']
                                                ),
                                                array(
                                                    'attribute' => 'sku',
                                                    'like' => $sku_array[0]
                                                ),
                                            )
                                        );
                                    $skuSearch = true;
                                }
                            }
                            if (!$skuSearch) {
                                $productsCollection = $productModel->getCollection()
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('sku')
                                    ->addAttributeToSelect('entity_id')
                                    ->addAttributeToFilter(
                                        'name', array('eq' => $item['title'])
                                    );//->addAttributeToFilter('synchronizedEtsy',0);
                            }
                            $queryProduct = $productsCollection->getData();

                            if ($queryProduct) {
                                $queryAux =
                                    $listingModel->getCollection()->addFieldToSelect('idproduct')->getSelect()->where(
                                        'idproduct = ?', $queryProduct[0]['id']
                                    );
                                $queryAux = $resource->getConnection('core_read')->fetchAll($queryAux);
                                if (!$queryAux) {
                                    $matchings = array(
                                        'etsy_id' => $item['listing_id'],
                                        'etsy_name' => $item['title'],
                                        'thumbnail' => $item['MainImage']['url_75x75'],
                                        'product_id' => $queryProduct[0]['entity_id'],
                                        'product_name' => $queryProduct[0]['name'],
                                        'product_sku' => $queryProduct[0]['sku']
                                    );
                                } else {
                                    $matchings = array(
                                        'etsy_id' => $item['listing_id'],
                                        'etsy_name' => $item['title'],
                                        'thumbnail' => $item['MainImage']['url_75x75'],
                                        'product_id' => null,
                                        'product_name' => null,
                                        'product_sku' => null
                                    );
                                }
                            } else {
                                $matchings = array(
                                    'etsy_id' => $item['listing_id'],
                                    'etsy_name' => $item['title'],
                                    'thumbnail' => $item['MainImage']['url_75x75'],
                                    'product_id' => null,
                                    'product_name' => null,
                                    'product_sku' => null
                                );
                            }
                            $changes = true;
                            $changesCount = $changesCount + 1;
                            $matchings['state'] = 0;
                            $mappingModel->setData($matchings);
                            $mappingModel->save();
                        }
                    }
                }
                if ($changes) {
                    return array(
                        'success' => true,
                        'count' => $changesCount
                    );
                } else {
                    if ($count == 0) {
                        return array(
                            'success' => true,
                            'count' => 0
                        );
                    } else {
                        return array(
                            'success' => false,
                            'count' => 0
                        );
                    }
                }
            } else {
                return array(
                    'success' => false,
                    'message' => $listingsResult['message']
                );
            }

        } catch (Exception $e) {
            $this->logException($e);

            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }


    /**
     * @return mixed
     */
    public function matchingListings()
    {
        try {
            $obligatory =
                array('shop_id' => Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop'));
            $params = array('includes' => 'MainImage');
            $listingsResult = $this->findAllShopListingsActive($obligatory, $params);
            $result = json_decode(json_decode($listingsResult['result']), true);
            $result = $result['results'];
            $changes = 0;
            foreach ($result as $item) {
                $mappingModel = Mage::getModel('magetsync/mappingEtsy');
                $queryM = $mappingModel->getCollection()->getSelect()->where('etsy_id = ?', $item['listing_id']);
                $queryM = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryM);
                if (!$queryM) {
                    $listingModel = Mage::getModel('magetsync/listing');
                    $query = $listingModel->getCollection()->getSelect()->where('listing_id = ?', $item['listing_id']);
                    $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                    if (!$query) {
                        $productsCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter(
                            'name', array('eq' => $item['title'])
                        );//->addAttributeToFilter('synchronizedEtsy',0);
                        $queryProduct = $productsCollection->getData();

                        if ($queryProduct) {
                            $listingModelAux = Mage::getModel('magetsync/listing');
                            $queryAux = $listingModelAux->getCollection()->getSelect()->where(
                                'idproduct = ?', $queryProduct[0]['id']
                            );
                            $queryAux =
                                Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryAux);
                            if (!$queryAux) {
                                $matchings = array(
                                    'etsy_id' => $item['listing_id'],
                                    'etsy_name' => $item['title'],
                                    'thumbnail' => $item['MainImage']['url_75x75'],
                                    'product_id' => $queryProduct[0]['entity_id'],
                                    'product_name' => $queryProduct[0]['name'],
                                    'product_sku' => $queryProduct[0]['sku']
                                );
                            } else {
                                $matchings = array(
                                    'etsy_id' => $item['listing_id'],
                                    'etsy_name' => $item['title'],
                                    'thumbnail' => $item['MainImage']['url_75x75'],
                                    'product_id' => null,
                                    'product_name' => null,
                                    'product_sku' => null
                                );
                            }
                        } else {
                            $matchings = array(
                                'etsy_id' => $item['listing_id'],
                                'etsy_name' => $item['title'],
                                'thumbnail' => $item['MainImage']['url_75x75'],
                                'product_id' => null,
                                'product_name' => null,
                                'product_sku' => null
                            );
                        }

                        $changes = true;

                        $mappingModel = Mage::getModel('magetsync/mappingEtsy');
                        $matchings['state'] = 0;
                        $mappingModel->setData($matchings);
                        $mappingModel->save();
                    }
                }
            }
            if ($changes) {
                return array('success' => true);
            } else {
                return array(
                    'success' => false,
                    'message' => 'There are not Etsy listings to match'
                );
            }

        } catch (Exception $e) {
            $this->logException($e);

            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * @param $productModel
     * @param $attributes
     * @return mixed
     */
    public function saveListingSynchronized($productModel, $attributes = null, $is_qty_validation = false)
    {
        try {
            $listingModel = Mage::getModel('magetsync/listing');
            $dataProduct = $productModel->getData();

            if ($dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ||
                $dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            ) {

                if ($attributes == null) {
                    $attributes = array();
                }

                $query = $listingModel->getCollection()->getSelect()->where('idproduct = ?', $dataProduct['entity_id']);
                $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);

                $dataSave = array(
                    'idproduct' => $dataProduct['entity_id'],
                    'sync' => Merchante_MagetSync_Model_Listing::STATE_INQUEUE
                );

                $this->handleQtyUpdate($dataProduct, $dataSave, $productModel);

                if ($is_qty_validation) {
                    if (array_key_exists('quantity', $dataSave) && $dataSave['quantity'] == 0) {
                        return array(
                            'success' => false,
                            'error' => 'This product can not be synchronized because has quantity 0.'
                        );
                    }
                }

                if (array_key_exists('price', $attributes)) {
                    $dataSave['price'] = $attributes['price'];
                } elseif ($query && $query[0]['is_custom_price'] == 1) {
                    $dataSave['price'] = $query[0]['price'];
                } else {
                    if (array_key_exists('special_price', $attributes)) {
                        $dataSave['price'] = $attributes['special_price'];
                    } else {
                        $dataSave['price'] = $dataProduct['price'];
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
                                                $dataSave['price'] = $dataProduct['special_price'];
                                            }
                                        } else {
                                            $dataSave['price'] = $dataProduct['special_price'];
                                        }
                                    }
                                } else {
                                    if ($dataProduct['special_to_date']) {
                                        $toDate = new DateTime($dataProduct['special_to_date']);
                                        if ($toDate >= $today) {
                                            $dataSave['price'] = $dataProduct['special_price'];
                                        }
                                    } else {
                                        $dataSave['price'] = $dataProduct['special_price'];
                                    }
                                }
                            }
                        }
                    }
                }

                $attrMetaKey = array_key_exists('meta_keyword', $attributes);
                if ($attrMetaKey) {
                    $text = $attributes['meta_keyword'];
                } else {
                    $text = $dataProduct['meta_keyword'];
                }
                $dataSplit = explode(',', $text);
                $dataTag = '';
                $i = 1;
                if ($dataSplit != null && count($dataSplit) > 0) {
                    foreach ($dataSplit as $data) {
                        if (strlen($data) <= 20) {
                            if ($i <= 13) {
                                $dataTag = $dataTag . ',' . $data;
                                $i = $i + 1;
                            } else {
                                break;
                            }
                        }
                    }
                    if ($dataTag == '') {
                        $dataTag = $this->categoryProductsTags($dataProduct['entity_id']);
                    }
                } else {
                    $dataTag = $this->categoryProductsTags($dataProduct['entity_id']);
                }
                $dataSave['tags'] = substr($dataTag, 1);

                $attrTitle = array_key_exists('name', $attributes);
                if ($attrTitle) {
                    $dataSave['title'] = ucfirst($attributes['name']);
                } else {
                    $dataSave['title'] = ucfirst($dataProduct['name']);
                }


                if (array_key_exists('description', $attributes)) {
                    $textNoHtml = strip_tags($attributes['description'], '<br></br><br/><br />');
                } else {
                    $textNoHtml = strip_tags($dataProduct['description'], '<br></br><br/><br />');
                }
                $newDescription = preg_replace('/(<br>)|(<\/br>)|(<br\/>)|(<br \/>)/', PHP_EOL, $textNoHtml);
                $dataSave['description'] = $newDescription;

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
                        $customTitle = $productModel->getData($isCustomTitleAttribute);
                        if (!empty($customTitle)) {
                            $dataSave['title'] = ucfirst($customTitle);
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
                        $customDesc = $productModel->getData($isCustomDescriptionAttribute);

                        if (!empty($customDesc)) {
                            $textNoHtml = strip_tags($customDesc, '<br></br><br/><br />');
                            $newDescription =
                                preg_replace('/(<br>)|(<\/br>)|(<br\/>)|(<br \/>)/', PHP_EOL, $textNoHtml);
                            $dataSave['description'] = $newDescription;

                        }
                    }
                }
                if ($query == null) {
                    $listingModel->setData($dataSave);
                    $listingModel->save();

                    return array('success' => true);
                } else {

                    /* Condition for saving in massive */
                    $attrSync = array_key_exists('synchronizedEtsy', $attributes);
                    if ($attrSync) {
                        $syncEtsy = $attributes['synchronizedEtsy'];
                    } else {
                        $syncEtsy = $productModel->getData("synchronizedEtsy");
                    }

                    if ($syncEtsy == 1) {
                        $dataSave['enabled'] = Merchante_MagetSync_Model_Listing::LISTING_ENABLED;
                    }

                    $this->handleSyncStatusUpdate($dataSave, $query);

                    $dataSave['quantity_has_changed'] = Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED;
                    $listingModel
                        ->addData($dataSave)
                        ->setId($query[0]['id']);
                    $listingModel->save();

                    return array('success' => true);
                }
            } else {
                return array(
                    'success' => false,
                    'error' => 'Invalid product type.'
                );
            }
        } catch (Exception $e) {
            $this->logException("Error: " . print_r($e->getMessage(), true));

            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * @param $dataProduct
     * @param $dataSave
     * @param $productModel
     */
    public function handleQtyUpdate($dataProduct, &$dataSave, $productModel)
    {
        if ($dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {

            //is_in_stock -> stock_item
            $stockItem = $productModel->getStockItem();
            if ($stockItem && $stockItem->getIsInStock()) {
                /* When Magento duplicate a product, It is setting out of stock */
                if ($productModel->getData("is_duplicate") == true) {
                    $dataSave['quantity'] = 0;
                } else {
                    if ($stockItem && $stockItem->getQty() == 0) {
                        $dataSave['quantity'] = 0;
                    } elseif ($stockItem && $stockItem->getQty() > 999) {
                        /* If quantity is over 999, we set 999 in this field because that is
                        is max quantity allowed on Etsy*/
                        $dataSave['quantity'] = 999;
                    } else {
                        $dataSave['quantity'] = $stockItem->getQty();
                    }
                }
            } else {
                $stockInfo =
                    Mage::getModel('cataloginventory/stock_item')->loadByProduct($productModel['entity_id'])->getData();
                if ($stockInfo && $stockInfo['is_in_stock']) {
                    $dataSave['quantity'] = $stockInfo['qty'];
                } else {
                    /* We set 1 because on Etsy you cannot save one quantity */
                    $dataSave['quantity'] = 0;
                }
            }

        } elseif ($dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            /* Total quantity in configurable products */
            $itemStock = 0;
            foreach ($productModel->getTypeInstance(true)->getUsedProducts(null, $productModel) as $simple) {
                $dataSimple = $simple->getData();
                if ($dataSimple['is_in_stock']) {
                    $existStockItem = array_key_exists('stock_item', $dataSimple);
                    if ($existStockItem) {
                        $stock = $dataSimple['stock_item']['qty'];
                        $stock = round($stock, 2);
                        $itemStock += $stock;
                    } else {
                        $itemStock += 0;
                    }
                } else {
                    $itemStock += 0;
                }
                /* Synchronized all the associated products */
                //syncVal['synchronizedEtsy'] = true;
                if (!$simple->getData('synchronizedEtsy')) {
                    $simple->setData('synchronizedEtsy', true)->getResource()->saveAttribute(
                        $simple, 'synchronizedEtsy'
                    );
                    //$simple->addData($syncVal)->save();
                }
            }

            if ($itemStock > 999) {
                $dataSave['quantity'] = 999;
            } else {
                $dataSave['quantity'] = $itemStock;
            }
        }
    }

    /**
     * @param $dataSave
     * @param $query
     */
    public function handleSyncStatusUpdate(&$dataSave, $query)
    {
        if ($query[0]['sync'] != Merchante_MagetSync_Model_Listing::STATE_EXPIRED) {
            if ($query[0]['sync'] == Merchante_MagetSync_Model_Listing::STATE_SYNCED ||
                (($query[0]['sync'] == Merchante_MagetSync_Model_Listing::STATE_FAILED ||
                        $query[0]['sync'] == Merchante_MagetSync_Model_Listing::STATE_MAPPED) &&
                    $query[0]['listing_id'] != '')
            ) {
                $dataSave['sync'] = Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC;
            } else {
                if ($query[0]['sync'] == Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE) {
                    unset($dataSave['sync']);
                }
            }
        } else {
            $dataSave['sync'] = Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
        }
    }

    /**
     * @param $postData
     * @param null $data
     * @return null
     */
    public function getTaxonomyID($postData, $data = null)
    {
        $category =
            isset($postData['category_id']) ? $postData['category_id'] : (isset($data['category_id']) ? $data['category_id'] : null);
        $subcategory =
            isset($postData['subcategory_id']) ? $postData['subcategory_id'] : (isset($data['subcategory_id']) ? $data['subcategory_id'] : null);
        $subsubcategory =
            isset($postData['subsubcategory_id']) ? $postData['subsubcategory_id'] : (isset($data['subsubcategory_id']) ? $data['subsubcategory_id'] : null);
        $category4 =
            isset($postData['subcategory4_id']) ? $postData['subcategory4_id'] : (isset($data['subcategory4_id']) ? $data['subcategory4_id'] : null);
        $category5 =
            isset($postData['subcategory5_id']) ? $postData['subcategory5_id'] : (isset($data['subcategory5_id']) ? $data['subcategory5_id'] : null);
        $category6 =
            isset($postData['subcategory6_id']) ? $postData['subcategory6_id'] : (isset($data['subcategory6_id']) ? $data['subcategory6_id'] : null);
        $category7 =
            isset($postData['subcategory7_id']) ? $postData['subcategory7_id'] : (isset($data['subcategory7_id']) ? $data['subcategory7_id'] : null);


        if ($category7 != null && $category7 != "0") {
            $taxonomyID = $category7;
        } else {
            if ($category6 != null && $category6 != "0") {
                $taxonomyID = $category6;
            } else {
                if ($category5 != null && $category5 != "0") {
                    $taxonomyID = $category5;
                } else {
                    if ($category4 != null && $category4 != "0") {
                        $taxonomyID = $category4;
                    } else {
                        if ($subsubcategory != null && $subsubcategory != "0") {
                            $taxonomyID = $subsubcategory;
                        } else {
                            if ($subcategory != null && $subcategory != "0") {
                                $taxonomyID = $subcategory;
                            } else {
                                if ($category != null && $category != "0") {
                                    $taxonomyID = $category;
                                } else {
                                    $taxonomyID = null;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $taxonomyID;
    }


    /**
     * @param $result
     * @param $idProduct
     * @param $priceBase
     * @param $idListing
     * @param string $callType
     * @return array
     */
    public function saveDetails($result, $idProduct, $priceBase, $idListing, $callType = 'all')
    {
        try {
            $statusOperation = array(
                'status' => true,
                'msg' => ''
            );
            $productModel = Mage::getModel('catalog/product')->load($idProduct);
            $dataPro = $productModel->getData();

            if ($callType == 'all' || $callType == 'inventory') {
                $availabilityStock = array();

                if ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                    $options = $productModel->getOptions();
                } elseif ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $options = $productModel->getTypeInstance()->getConfigurableAttributesAsArray();
                    foreach ($productModel->getTypeInstance(true)->getUsedProducts(null, $productModel) as $simpleAux) {

                        $dataSimple = $simpleAux->getData();
                        if ($dataSimple['is_in_stock']) {
                            $existStockItem = array_key_exists('stock_item', $dataSimple);
                            if ($existStockItem) {
                                if ($dataSimple['stock_item']['qty'] > 0) {
                                    $availabilityStock[] = true;
                                } else {
                                    $availabilityStock[] = false;
                                }
                            } else {
                                $availabilityStock[] = false;
                            }
                        } else {
                            $availabilityStock[] = false;
                        }
                    }

                }

                /******************************
                 * Variations create section
                 ******************************/

                $variationModel = Mage::getModel('magetsync/variation')->getCollection()->getData();
                $nCustom = 0;
                $obliVariation['listing_id'] = $result['listing_id'];
                $scalesArray = array();
                $variationMapping = array();
                $requestParams = array();

                foreach ($options as $valueVar) {

                    if ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                        $dataValue = $valueVar->getData();
                        $exist = $this->searchForName(ucfirst($dataValue['title']), $variationModel);
                        $valuesOpt = $valueVar->getValues();
                        $propertyName = ucfirst($dataValue['title']);
                    } elseif ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                        $dataValue = $valueVar;
                        $exist = $this->searchForName(ucfirst($dataValue['label']), $variationModel, 'label');
                        $valuesOpt = $valueVar['values'];
                        $propertyName = ucfirst($dataValue['label']);
                    }
                    $scaleValue = 0;
                    if ($exist <> -1) {
                        $propertyID = $variationModel[$exist]['propertyid'];

                        if ($propertyID == 504 || $propertyID == 501 || $propertyID == 505 || $propertyID == 506
                            || $propertyID == 100 || $propertyID == 511 || $propertyID == 512
                        ) {
                            if ($propertyName == 'Size') {
                                $propertyScaleName = 'sizing';
                            } else {
                                $propertyScaleName = strtolower($propertyName);
                            }
                            $scaleName = $propertyScaleName . '_scale';

                            $scaleValue = Mage::getStoreConfig(
                                'magetsync_section/magetsync_group_variations/magetsync_field_' . $propertyScaleName .
                                '_scale'
                            );

                            $scalesArray[$scaleName] = $scaleValue;
                        }

                    } else {
                        /* 513 and 514 are custom properties on Etsy */
                        if ($nCustom == 0) {
                            $propertyID = 513;

                        } elseif ($nCustom == 1) {
                            $propertyID = 514;

                        } else {
                            break;
                        }
                    }

                    $y = 0;
                    foreach ($valuesOpt as $item) {
                        $variationMapping[$valueVar['attribute_code']][$item['value_index']]['price'] = $item['pricing_value'];
                        $variationMapping[$valueVar['attribute_code']][$item['value_index']]['is_percent'] = $item['is_percent'];
                        $variationMapping[$valueVar['attribute_code']][$item['value_index']]['property_name'] = $valueVar['frontend_label'];
                        $variationMapping[$valueVar['attribute_code']][$item['value_index']]['value'] = $item['label'];
                        $variationMapping[$valueVar['attribute_code']][$item['value_index']]['property_id'] = $propertyID;

                        if ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ||
                            $dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                        ) {
                            $matches = null;
                            if ($propertyID == 504 || $propertyID == 501 || $propertyID == 505 || $propertyID == 506
                                || $propertyID == 100 || $propertyID == 511 || $propertyID == 512
                            ) {
                                if ($scaleValue != 343 && $scaleValue != 346 && $scaleValue != 349 &&
                                    $scaleValue != 352 && $scaleValue != 329 && $scaleValue != 340
                                ) {
                                    preg_match('/^\D*(\d+(?:[\.|\,]\d+)?)/', $variationMapping[$valueVar['attribute_code']][$item['value_index']]['value'], $matches);
                                }
                            }
                            if ($matches && count($matches) > 0) {
                                //$singleVariation['value'] = $matches[1];
                                $variationMapping[$valueVar['attribute_code']][$item['value_index']]['value'] = $matches[1];
                            }
                        }

                        $y = $y + 1;
                    }

                    if ($exist == -1) {
                        $nCustom = $nCustom + 1;
                    }
                }

                $productsData = array();
                if ($productModel->getTypeId() == "configurable") {
                    $confProduct = Mage::getModel('catalog/product_type_configurable')->setProduct($productModel);
                    $simpleCollection = $confProduct->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                    foreach ($simpleCollection as $simpleProduct) {
                        $product = array();
                        $product['property_values'] = array();
                        $product['sku'] = $simpleProduct->getSku();
                        $priceVal = 0;
                        foreach ($variationMapping as $attrCode => $attrValsArr) {
                            if ($attrValsArr[$simpleProduct[$attrCode]]['is_percent'] == '1') {
                                $priceVal += $singleVariation['price'] = ($priceBase * ($attrValsArr[$simpleProduct[$attrCode]]['price'] / 100)) + $priceBase;
                            } else {
                                $priceVal += $attrValsArr[$simpleProduct[$attrCode]]['price'];
                            }
                            $product['property_values'][] = array(
                                'property_id' => $attrValsArr[$simpleProduct[$attrCode]]['property_id'],
                                'property_name' => $attrValsArr[$simpleProduct[$attrCode]]['property_name'],
                                'value' => $attrValsArr[$simpleProduct[$attrCode]]['value']
                            );
                        }
                        $product['offerings'] = array(array(
                            'price' => $priceBase + $priceVal,
                            'quantity' => $simpleProduct->getStockItem() ? intval($simpleProduct->getStockItem()->getQty()) : 0,
                            'is_enabled' => intval($simpleProduct->getIsInStock())
                        ));
                        $requestParams[] = $product;
                    }
                    $productsData['price_on_property'] = array($propertyID);
                    $productsData['quantity_on_property'] = array($propertyID);
                    $productsData['sku_on_property'] = array($propertyID);
                    $productsData['products'] = json_encode($requestParams, 128);

                    $resultVariationApi = Mage::getModel('magetsync/variation')->updateInventory($obliVariation, $productsData);

                    if ($resultVariationApi['status'] == true) {
                        $resultVariation = json_decode(json_decode($resultVariationApi['result']), true);
                    } else {
                        Merchante_MagetSync_Model_LogData::magetsync(
                            $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                            $resultVariationApi['message'], Merchante_MagetSync_Model_LogData::LEVEL_WARNING
                        );
                        return array(
                            'status' => false,
                            'message' => 'Unable to update inventory.'
                        );
                    }
                }
            }
            
            if ($callType == 'all' || $callType == 'image') {
                /**********************************/
                $h = 0;
                /******************************
                 * Upload Image section
                 *****************************/
                $newImages = array();
                if (count($dataPro['media_gallery']['images']) > 0) {
                    $excluded = Mage::getStoreConfig(
                        'magetsync_section/magetsync_group_options/magetsync_field_exclude_pictures'
                    );
                    if ($excluded == '1') {
                        foreach ($dataPro['media_gallery']['images'] as $imageAux) {
                            if ($imageAux['disabled'] != '1') {
                                $newImages[] = $imageAux;
                                if ($result['listing_id']) {
                                    $imageModel = Mage::getModel('magetsync/imageEtsy')->getCollection();
                                    $queryVerify = $imageModel->getSelect()->where('file = ?', $imageAux['file']);
                                    $queryVerify =
                                        Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll(
                                            $queryVerify
                                        );
                                    if ($queryVerify) {
                                        $obligatoryDelete = array(
                                            'listing_id' => $result['listing_id'],
                                            'listing_image_id' => intval($queryVerify[0]['listing_image_id'])
                                        );
                                        $resultImageApiDelete = Mage::getModel('magetsync/listing')->deleteListingImage(
                                            $obligatoryDelete, null
                                        );
                                        if ($resultImageApiDelete['status']) {
                                            $resultDeleteVerify =
                                                Mage::getModel('magetsync/imageEtsy')->setId($queryVerify[0]['id'])
                                                    ->delete();
                                        } else {
                                            Merchante_MagetSync_Model_LogData::magetsync(
                                                $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                                $resultImageApiDelete['message'],
                                                Merchante_MagetSync_Model_LogData::LEVEL_WARNING
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $newImages = $dataPro['media_gallery']['images'];
                    }

                    //We sort and cut the array of images
                    $imageUrl = $productModel->getImage();
                    $resultIndex = $this->searchForFile($imageUrl, $newImages);
                    if (isset($resultIndex)) {
                        $valueDelete = $newImages[$resultIndex];
                        unset($newImages[$resultIndex]);
                        //arsort($newImages);
                        usort(
                            $newImages, function ($a, $b) {
                            return strcmp($a->position, $b->position);
                        }
                        );
                        if (count($newImages) >= 5) {
                            $newImages = array_slice($newImages, 0, 4);
                        }
                        array_push($newImages, $valueDelete);
                    }
                }

                $paramImg = array('listing_id' => $result['listing_id']);
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

                foreach ($newImages as $image) {
                    //We control that the number of images always
                    //be 5 or less (Etsy restriction)
                    if ($h < (5 - $totalImages)) {
                        $imageModel = Mage::getModel('magetsync/imageEtsy')->getCollection();
                        $query = $imageModel->getSelect()->where('file = ?', $image['file']);
                        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                        $file = Mage::getBaseDir('media') . '/catalog/product' . $image['file'];
                        $info = pathinfo($file);
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
                        if ($resultUpload['success'] == true) {
                            $file = $resultUpload['upload'];
                            if ($query == null) {
                                $params = array(
                                    '@image' => '@' . $file . ';type=' . $mime,
                                    'name' => $file
                                );
                            } else {
                                $params = array(
                                    '@image' => '@' . $file . ';type=' . $mime,
                                    'listing_image_id' => intval($query[0]['listing_image_id']),
                                    'name' => $file
                                );
                                $obligatoryDelete = array(
                                    'listing_id' => $result['listing_id'],
                                    'listing_image_id' => intval($query[0]['listing_image_id'])
                                );
                                $resultImageApiDelete =
                                    Mage::getModel('magetsync/listing')->deleteListingImage($obligatoryDelete, null);
                                if (!$resultImageApiDelete['status']) {
                                    Merchante_MagetSync_Model_LogData::magetsync(
                                        $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                        $resultImageApiDelete['message'],
                                        Merchante_MagetSync_Model_LogData::LEVEL_WARNING
                                    );

                                }
                            }
                            $resultImageApi =
                                Mage::getModel('magetsync/listing')->uploadListingImage($obligatory, $params);

                            if ($resultImageApi['status']) {
                                $resultImage = json_decode(json_decode($resultImageApi['result']), true);
                                $resultImage = $resultImage['results'][0];
                                $imageData = array(
                                    'listing_id' => $resultImage['listing_id'],
                                    'listing_image_id' => $resultImage['listing_image_id'],
                                    'file' => $image['file']
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
                            } else {
                                throw new Exception($resultImageApi['message']);
                            }
                        } else {

                            Merchante_MagetSync_Model_LogData::magetsync(
                                $idListing, Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                                $resultUpload['message'], Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                            );

                        }
                        $h = $h + 1;
                    }
                }
            }

            return array('status' => $statusOperation);
        } catch (Exception $e) {
            Mage::logException($e);

            return array(
                'status' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * @param $postData
     * @param $data
     * @return string
     */
    public function emptyField($postData, $data, $isBool = null)
    {
        return (isset($postData) && !empty($postData)) ? $postData : ((isset($data) &&
            !empty($data)) ? $data : $isBool);
    }

    /**
     * Method internal for returning un key value
     * @param $id
     * @param $array
     * @return int|string
     */
    public function searchForFile($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['file'] === $id) {
                return $key;
            }
        }

        return -1;
    }

    /**
     * Appends/prepends global notes to listing description
     * @param $oldDescription
     * @param $prependedTemplate
     * @param $appendedTemplate
     * @param $productId
     * @return mixed|string
     */
    public function composeDescription($oldDescription, $prependedTemplate, $appendedTemplate, $productId)
    {
        $productData = Mage::getModel('catalog/product')->load($productId)->getData();
        $newDescription = $oldDescription;

        if ($prependedTemplate) {
            $descriptionTemplateRaw = false;
            switch ($prependedTemplate) {
                case 1:
                    $descriptionTemplateRaw = Mage::getStoreConfig('magetsync_section_templates/magetsync_group_notes_1/magetsync_field_prepend_one');
                    break;
                case 2:
                    $descriptionTemplateRaw = Mage::getStoreConfig('magetsync_section_templates/magetsync_group_notes_2/magetsync_field_prepend_two');

                    break;
            }
            if ($descriptionTemplateRaw) {
                $descriptionTemplate = $this->replaceAttributePatterns($descriptionTemplateRaw, $productData);
                $textNoHtml = strip_tags($descriptionTemplate, '<br></br><br/><br />');
                $newDescription = preg_replace('/(<br>)|(<\/br>)|(<br\/>)|(<br \/>)/', PHP_EOL, $textNoHtml);
                $newDescription .= PHP_EOL . $oldDescription;
            }
        }

        if ($appendedTemplate) {
            $descriptionTemplateRaw = false;
            switch ($appendedTemplate) {
                case 1:
                    $descriptionTemplateRaw = Mage::getStoreConfig('magetsync_section_templates/magetsync_group_notes_1/magetsync_field_append_one');
                    break;
                case 2:
                    $descriptionTemplateRaw = Mage::getStoreConfig('magetsync_section_templates/magetsync_group_notes_2/magetsync_field_append_two');

                    break;
            }
            if ($descriptionTemplateRaw) {
                $descriptionTemplate = $this->replaceAttributePatterns($descriptionTemplateRaw, $productData);
                $textNoHtml = strip_tags($descriptionTemplate, '<br></br><br><br />');
                $newDescriptionAppend = preg_replace('/(<br>)|(<\/br>)|(<br\/>)|(<br \/>)/', PHP_EOL, $textNoHtml);
                $newDescription .= PHP_EOL . $newDescriptionAppend;
            }
        }

        return $newDescription;
    }

    /**
     * Replaces entries surrounded with curly brackets with product attribute values
     * @param $text
     * @param $productData
     * @return string
     */
    public function replaceAttributePatterns($text, $productData)
    {
        $returnText = $text;
        $regexp = '/\{\{.*?\}\}/';
        preg_match_all($regexp, $text, $matches);
        $attributeValArr = array();
        foreach ($matches[0] as $matchedPattern) {
            $attributeCode = str_replace(array('{{', '}}'), '', $matchedPattern);
            $attributeValArr[$attributeCode] = $productData[$attributeCode];
        }
        foreach ($attributeValArr as $attrCode => $attrVal) {
            $returnText = str_replace('{{' . $attrCode . '}}', $attrVal, $returnText);
        }

        return $returnText;
    }

    /**
     * @param $idProduct
     * @return string
     */
    function categoryProductsTags($idProduct)
    {
        $dataTag = '';
        $i = 1;
        $products = Mage::getModel('catalog/product')->load($idProduct);
        $categoryIds = $products->getCategoryIds();
        if ($categoryIds != null && count($categoryIds) > 0) {
            foreach ($categoryIds as $category) {
                $categoryAux = Mage::getModel('catalog/category')->load($category);
                if (strlen($categoryAux->name) <= 20) {
                    if ($i <= 13) {
                        $dataTag = $dataTag . ',' . $categoryAux->name;
                        $i = $i + 1;
                    } else {
                        break;
                    }
                }
            }
        }

        return $dataTag;
    }

    /**
     * @param $id
     * @param $array
     * @return int|string
     */
    function searchForName($id, $array, $keyName = 'name')
    {
        foreach ($array as $key => $val) {
            if ($val[$keyName] === $id) {
                return $key;
            }
        }

        return -1;
    }

    /**
     * Forces listing product qty and status update if update hasn't been logged
     */
    function triggerUpdate()
    {
        try {
            $productModel = Mage::getModel('catalog/product')->load($this->getIdproduct());
            $dataProduct = $productModel->getData();
            $dataSave = array('idproduct' => $dataProduct['entity_id']);
            $mockedQuery = array(0 => array('sync' => $this->getSync()));

            $parent =
                Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($dataProduct['entity_id']);
            if (!$parent
                && ($dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                    || $dataProduct['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            ) {

                $this->handleQtyUpdate($dataProduct, $dataSave, $productModel);
                $this->handleSyncStatusUpdate($dataSave, $mockedQuery);
                $this->addData($dataSave)->save();
            }
        } catch (Exception $e) {
            $this->logException($e);

            return;
        }
    }

    public function getPreparedDataToSend()
    {
        $data = $this->getData();

        if ($data['listing_id']) {
            throw new Exception();
        }
    }
}