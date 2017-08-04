<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class observer for catalog products
 * Class Mod_Products_Model_Observer
 */
class Merchante_MagetSync_Model_Product_Observer
{

    /**
     * Method observer for after saving product
     * @param $observer
     */
    public function logUpdate($observer, $idProduct = null, $attributes = null)
    {
        try {
            if ($idProduct == null) {
                $product = $observer->getEvent()->getProduct();
                $data = $product->getData();

                $status = (isset($data['synchronizedEtsy']) ? $data['synchronizedEtsy'] : null);
            } else {
                $product = Mage::getModel('catalog/product')->load($idProduct);
                $data = $product->getData();
                $status = $attributes['synchronizedEtsy'];
                if (is_null($status)) {
                    $status = $data['synchronizedEtsy'];
                }
            }
            /** @var Mage_Catalog_Model_Product $parent */
            $parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($data['entity_id']);


            if (!$parent) {
                $productModel = $product;
            } else {
                $productModel = Mage::getModel('catalog/product')->load($parent[0]);
            }
            if ($status == 1) {
                Mage::getModel('magetsync/listing')->saveListingSynchronized($productModel, $attributes, false);
            } elseif ($status == 0) {
                //If the product was in queue delete this.
                $listingModel = Mage::getModel('magetsync/listing');
                $query = $listingModel->getCollection()->getSelect()->where('idproduct = ?', $data['entity_id']);
                $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                if ($query != null) {
                    if ($query[0]['sync'] == Merchante_MagetSync_Model_Listing::STATE_INQUEUE) {
                        $listingModel->setId($query[0]['id'])
                            ->delete();
                    } elseif ($query[0]['sync'] != Merchante_MagetSync_Model_Listing::STATE_INQUEUE) {
                        $dataSave['enabled'] = Merchante_MagetSync_Model_Listing::LISTING_DISABLED;
                        $listingModel
                            ->addData($dataSave)
                            ->setId($query[0]['id']);
                        $listingModel->save();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);

            return;
        }
    }

    public function massiveUpdate($observer)
    {
        try {

            $parentProductIDs = array();
            $data = $observer->getEvent()->getData();
            $listingModel = Mage::getModel('magetsync/listing');
            $requestProductIDs = $data['product_ids'] ?: $data['products'];
            foreach ($requestProductIDs as $productID) {
                $parentProductIDs = array_merge($parentProductIDs, Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productID));
            }
            $allProductIDs = array_merge($requestProductIDs, $parentProductIDs);
            $existingListings = $listingModel->getCollection()->addFieldToSelect('*')->addFieldToFilter(
                'idproduct', array('in' => $allProductIDs)
            )->load();
            foreach ($existingListings as $listing) {
                $this->logUpdate(null, $listing->getIdproduct(), $data['attributes_data']);
            }
        } catch (Exception $e) {
            Mage::logException($e);

            return;
        }
    }

}