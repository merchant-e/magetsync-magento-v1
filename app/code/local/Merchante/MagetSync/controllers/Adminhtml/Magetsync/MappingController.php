<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * Main Controller section adminhtml
 * Class Merchante_Magetsync_Adminhtml_MappingController
 */
class Merchante_MagetSync_Adminhtml_Magetsync_MappingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Method for rendering Layout
     */
    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function initMappingAction()
    {
        $offset = $this->getRequest()->getParam('offset');
        $result = Mage::getModel('magetsync/listing')->matchingListingsAux($offset);
        $data = json_encode($result, true);
        echo $data;
    }

    public function acceptAllAction()
    {
        try {
            $idsProductsAux = $this->getRequest()->getParam('idsProducts');
            $idsListingsAux = $this->getRequest()->getParam('idsListings');
            $idsProducts = explode(',', $idsProductsAux);
            $idsListings = explode(',', $idsListingsAux);
            $i = 0;
            $mappingModel = Mage::getModel('magetsync/mappingEtsy');
            foreach ($idsListings as $listing) {
                $listingModel = Mage::getModel('magetsync/listing');
                $listingCollection = $listingModel->getCollection();
                $query = $listingCollection->getSelect()->where('listing_id= ?', $listing);
                $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                $resultListing['status'] = true;
                if (!$query) {
                    $obligParam = array('listing_id' => $listing);
                    $resultListing = $listingModel->getListing($obligParam);
                    if (($resultListing['status'])) {
                        $this->setListingInformation($resultListing, $idsProducts[$i]);
                    } else {
                        //$json = array('message'=>$resultListing['msg'],"status"=>true);
                        Mage::log("Error: " . print_r($resultListing['msg'], true), null, 'magetsync_mapping.log');
                    }
                }
                if ($resultListing['status']) {
                    $collectionProduct = Mage::getModel('catalog/product')->getCollection()
                                             ->addAttributeToSelect(
                                                 array(
                                                     'sku',
                                                     'name'
                                                 ), 'inner'
                                             )
                                             ->addAttributeToFilter('entity_id', $idsProducts[$i])
                                             ->load();
                    $productName = '';
                    $productSKU = '';
                    if ($collectionProduct) {
                        $collectionData = $collectionProduct->getData();
                        $productName = $collectionData[0]['name'];
                        $productSKU = $collectionData[0]['sku'];
                    }

                    $matchings['etsy_id'] = $listing;
                    $matchings['product_id'] = $idsProducts[$i];
                    $matchings['product_name'] = $productName;
                    $matchings['product_sku'] = $productSKU;
                    $matchings['state'] = 1;

                    $mappingCollection = $mappingModel->getCollection()
                                                      ->addFieldToSelect(array('id'))
                                                      ->addFieldToFilter('etsy_id', $listing)->load();

                    $mappingData = $mappingCollection->getData();
                    if ($mappingData) {
                        $mappingModel
                            ->addData($matchings)
                            ->setId($mappingData[0]['id']);
                        $mappingModel->save();
                    }

                }

                $i = $i + 1;
            }
            $json = array(
                'message' => '',
                "status"  => true
            );
            $data = json_encode($json, true);
            echo $data;

        } catch (Exception $e) {
            $json = array(
                'message' => $e->getMessage(),
                "status"  => false
            );
            $data = json_encode($json, true);
            echo $data;
        }
    }

    public function loadProductsAction()
    {
        try {
            $productsCollection = Mage::getModel('catalog/product')->getCollection()
                                      ->addAttributeToSelect('name')
                                      ->addAttributeToSelect('entity_id')
                                      ->addAttributeToSelect('sku')
                                      ->addAttributeToFilter(
                                          'type_id', array(
                                          'in' => array(
                                              'simple',
                                              'configurable'
                                          )
                                      )
                                      )->addAttributeToFilter(
                    array(
                        array(
                            'attribute' => 'synchronizedEtsy',
                            'null'      => true
                        ),
                        array(
                            'attribute' => 'synchronizedEtsy',
                            'eq'        => '0'
                        ),
                        array(
                            'attribute' => 'synchronizedEtsy',
                            'eq'        => ''
                        )
                    ), '', 'left'
                )->load(
                );//'synchronizedEtsy', array('null'=>true),'left')->addAttributeToFilter('synchronizedEtsy', array('eq'=>false));
            $rows = '';

            foreach ($productsCollection as $item) {
                $data = $item->getData();
                $rows = $rows . '
                    <tr>
                        <td>' . $data['entity_id'] . '</td><td>' . $data['name'] . '</td><td>' . $data['sku'] . '</td>
                        <td class=\'footable-last-column\'><a class=\'row-match-product\' href=\'#\'>
                        <span class=\'glyphicon glyphicon-check\'></span></a></td>
                    </tr>';
            }

            if (count($productsCollection) > 0) {
                $json = array(
                    'data'   => $rows,
                    "status" => true
                );
            } else {
                $json = array(
                    'data'   => 'There aren\'t products available',
                    "status" => false
                );
            }
            $result = json_encode($json, true);
            echo $result;
        } catch (Exception $e) {
            $json = array(
                'data'   => $e->getMessage(),
                "status" => false
            );
            $result = json_encode($json, true);
            echo $result;
        }

    }

    public function startMappingAction()
    {
        $result = Mage::getModel('magetsync/listing')->matchingListings();
        if ($result['success']) {
            $json = array("status" => true);

        } else {
            $json = array(
                'message' => $result['message'],
                "status"  => false
            );
        }
        $data = json_encode($json, true);
        echo $data;

    }

    public function cancelMappingAction()
    {
        try {

            $product = $this->getRequest()->getParam('productID');
            $listing = $this->getRequest()->getParam('listingID');
            $process = $this->getRequest()->getParam('process');
            $productID = '';
            $listingID = '';
            switch ($process) {
                case 'all':
                    $productID = str_replace('all-node-', '', $product);
                    $listingID = str_replace('all-etsy-', '', $listing);
                    break;
                case 'possible':
                    $productID = str_replace('possible-node-', '', $product);
                    $listingID = str_replace('possible-etsy-', '', $listing);
                    break;
                case 'already':
                    $productID = str_replace('already-node-', '', $product);
                    $listingID = str_replace('already-etsy-', '', $listing);
                    break;
            }


            $listingModel = Mage::getModel('magetsync/listing');
            $listingCollection = $listingModel->getCollection();
            $query = $listingCollection->getSelect()->where('listing_id= ?', $listingID);
            $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
            if ($query) {
                if ($query[0]['sync'] != Merchante_MagetSync_Model_Listing::STATE_SYNCED) {
                    $listingModel->setId($query[0]['id'])
                                 ->delete();

                    $mappingModel = Mage::getModel('magetsync/mappingEtsy');
                    $mappingCollection = $mappingModel->getCollection();
                    $query = $mappingCollection->getSelect()->where('etsy_id= ?', $listingID);
                    $queryMapping = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                    if ($queryMapping) {
                        $matching['state'] = 0;
                        $mappingModel
                            ->addData($matching)
                            ->setId($queryMapping[0]['id']);
                        $mappingModel->save();
                    }

                    $productModel = Mage::getModel('catalog/product');
                    $productData['synchronizedEtsy'] = false;
                    $productModel
                        ->addData($productData)
                        ->setId($productID);
                    $productModel->save();

                    $json = array(
                        'message' => '',
                        "status"  => true
                    );
                    $data = json_encode($json, true);
                    echo $data;
                } else {
                    $json = array(
                        'message' => 'You can not delete the listing because is already synchronized',
                        "status"  => false
                    );
                    $data = json_encode($json, true);
                    echo $data;
                }
            } else {
                $json = array(
                    'message' => 'Listing doesn\'t exist ',
                    "status"  => false
                );
                $data = json_encode($json, true);
                echo $data;
            }
        } catch (Exception $e) {
            $json = array(
                'message' => $e->getMessage(),
                "status"  => false
            );
            $data = json_encode($json, true);
            echo $data;
        }
    }

    public function setMappingAction()
    {
        try {

            $product = $this->getRequest()->getParam('productID');
            $listing = $this->getRequest()->getParam('listingID');
            $productNameAux = $this->getRequest()->getParam('productNAME');
            $productName = str_replace('-a909a-', '/', $productNameAux);
            $productArray = explode('/(', $productName);
            $productName = $productArray[0];
            $productSKU = substr($productArray[1], 0, strlen($productArray[1]) - 1);

            $process = $this->getRequest()->getParam('process');
            $productID = '';
            $listingID = '';
            switch ($process) {
                case 'all':
                    $productID = str_replace('all-node-', '', $product);
                    $listingID = str_replace('all-etsy-', '', $listing);
                    break;
                case 'possible':
                    $productID = str_replace('possible-node-', '', $product);
                    $listingID = str_replace('possible-etsy-', '', $listing);
                    break;
                case 'already':
                    $productID = str_replace('already-node-', '', $product);
                    $listingID = str_replace('already-etsy-', '', $listing);
                    break;
            }
            $mappingModel = Mage::getModel('magetsync/mappingEtsy');
            $mappingCollection = $mappingModel->getCollection();
            $query = $mappingCollection->getSelect()->where('etsy_id= ?', $listingID);
            $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
            $json = array(
                'message' => '',
                "status"  => true
            );
            if ($query) {
                $listingModel = Mage::getModel('magetsync/listing');
                $listingModelCollection = $listingModel->getCollection();
                $queryListing = $listingModelCollection->getSelect()->where('listing_id= ?', $listingID);
                $queryListing =
                    Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryListing);
                $resultListing['status'] = true;
                if (!$queryListing) {
                    $obligParam = array('listing_id' => $listingID);
                    $resultListing = $listingModel->getListing($obligParam);

                    if (($resultListing['status'])) {
                        $this->setListingInformation($resultListing, $productID);
                    } else {
                        $json = array(
                            'message' => $resultListing['msg'],
                            "status"  => true
                        );
                    }
                }
                if ($resultListing['status']) {
                    $queryProductAux = $mappingCollection->getSelect()->where('product_id= ?', $productID);
                    $queryProduct =
                        Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryProductAux);
                    if ($queryProduct) {
                        if ($query[0]['etsy_id'] != $queryProduct[0]['etsy_id']) {
                            if ($query[0]['product_id'] != $productID) {
                                $matchingsAux['product_name'] = $query[0]['product_name'];
                                $matchingsAux['product_sku'] = $query[0]['product_sku'];
                                $matchingsAux['product_id'] = $query[0]['product_id'];
                            } else {
                                $matchingsAux['product_name'] = '';
                                $matchingsAux['product_sku'] = '';
                                $matchingsAux['product_id'] = '';
                            }
                            $matchingsAux['etsy_id'] = $queryProduct[0]['etsy_id'];
                            $mappingModel
                                ->addData($matchingsAux)
                                ->setId($queryProduct[0]['id']);
                            $mappingModel->save();
                        }
                    }

                    $matchings['etsy_id'] = $listingID;
                    $matchings['product_id'] = $productID;
                    $matchings['product_name'] = $productName;
                    $matchings['product_sku'] = $productSKU;
                    $matchings['state'] = 1;


                    $mappingModel
                        ->addData($matchings)
                        ->setId($query[0]['id']);
                    $mappingModel->save();

                }

                $data = json_encode($json, true);
                echo $data;

            } else {
                $json = array(
                    'message' => 'Listing doesn\'t exist',
                    "status"  => false
                );
                $data = json_encode($json, true);
                echo $data;
            }
        } catch (Exception $e) {
            $json = array(
                'message' => $e->getMessage(),
                "status"  => false
            );
            $data = json_encode($json, true);
            echo $data;
        }

    }

    public function setListingInformation($resultListing, $productID)
    {
        $result = json_decode(json_decode($resultListing['result']), true);
        $result = $result['results'][0];

        $tags = implode(',', $result['tags']);
        $materials = implode(',', $result['materials']);

        $postData['idproduct'] = $productID;
        $postData['creation_tsz'] = $result['creation_tsz'];
        $postData['ending_tsz'] = $result['ending_tsz'];
        $postData['original_creation_tsz'] = $result['original_creation_tsz'];
        $postData['last_modified_tsz'] = $result['last_modified_tsz'];
        $postData['currency_code'] = $result['currency_code'];
        $postData['featured_rank'] = $result['featured_rank'];
        $postData['description'] = $result['description'];
        $postData['title'] = $result['title'];
        $postData['tags'] = $tags;
        $postData['materials'] = $materials;
        $postData['shop_section_id'] = $result['shop_section_id'];
        $postData['shipping_template_id'] = $result['shipping_template_id'];
        $postData['quantity'] = $result['quantity'];
        $postData['url'] = $result['url'];
        $postData['who_made'] = $result['who_made'];
        $postData['when_made'] = $result['when_made'];
        $postData['is_supply'] = $result['is_supply'];
        $postData['price'] = $result['price'];
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
        $postData['quantity_has_changed'] = Merchante_MagetSync_Model_Listing::QUANTITY_HAS_NOT_CHANGED;
        $postData['enabled'] = Merchante_MagetSync_Model_Listing::LISTING_ENABLED;
        $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_MAPPED;

        /**CATEGORIES**/

        $categoryModel = Mage::getModel('magetsync/category');
        $categoryCollection = $categoryModel->getCollection();
        $queryTaxonomy = $categoryCollection->getSelect()->where('level_id= ?', $result['taxonomy_id']);
        $queryTaxonomy = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryTaxonomy);

        if ($queryTaxonomy) {
            $this->recursiveTaxonomy($queryTaxonomy[0], $postData);
        }

        $listingModel = Mage::getModel('magetsync/listing');
        $listingModel->setData($postData);
        $listingModel->save();

        $productModel = Mage::getModel('catalog/product');

        $productData['synchronizedEtsy'] = true;
        $productModel
            ->addData($productData)
            ->setId($productID);
        $productModel->save();


    }

    public function recursiveTaxonomy($category, &$postData)
    {
        switch ($category['level']) {
            case 0:
                $postData['category_id'] = $category['level_id'];
                break;
            case 1:
                $postData['subcategory_id'] = $category['level_id'];
                break;
            case 2:
                $postData['subsubcategory_id'] = $category['level_id'];
                break;
            case 3:
                $postData['subcategory4_id'] = $category['level_id'];
                break;
            case 4:
                $postData['subcategory5_id'] = $category['level_id'];
                break;
            case 5:
                $postData['subcategory6_id'] = $category['level_id'];
                break;
            case 6:
                $postData['subcategory7_id'] = $category['level_id'];
                break;
            default:
                return;
        }
        if ($category['parent_id'] == null) {
            return;
        } else {
            $categoryModel = Mage::getModel('magetsync/category');
            $categoryCollection = $categoryModel->getCollection();
            $queryTaxonomy = $categoryCollection->getSelect()->where('level_id= ?', $category['parent_id']);
            $queryTaxonomy = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryTaxonomy);
            if ($queryTaxonomy) {
                $this->recursiveTaxonomy($queryTaxonomy[0], $postData);
            }
        }
    }

    /**
     * Check if user has permissions to visit page
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/magetsync/mapping');
    }
}