<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class observer for catalog products
 * Class Mod_Products_Model_Observer
 */
class Merchante_MagetSync_Model_Product_Observer {

    /**
     * Method observer for after saving product
     * @param $observer
     */
    public function logUpdate($observer,$idProduct = null, $attributes = null) {
        try{
        if($idProduct == null)
        {
            $product = $observer->getEvent()->getProduct();
            $data = $product->getData();
            if($data['visibility'] == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
            {
               return;
            }
            //Mage::log("Error: ".print_r($data, true),null,'magetsync_productX.log');
            $status = (isset($data['synchronizedEtsy'])?$data['synchronizedEtsy']:null);
        }else{
            $product = Mage::getModel('catalog/product')->load($idProduct);
            $data = $product->getData();
            $status = $attributes['synchronizedEtsy'];
        }
        $parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($data['entity_id']);

        if(!$parent) {
            if ($status == 1) {
                $result = Mage::getModel('magetsync/listing')->saveListingSynchronized($product, $attributes, false);
            }elseif ($status == 0) {
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
        }
        } catch (Exception $e){
            Mage::logException($e);
            return;
        }
    }

    public function massiveUpdate($observer) {
        try{
            $data = $observer->getEvent()->getData();
            $product_ids = $data['product_ids'];
            $attributes = $data['attributes_data'];
            $value = array_key_exists('synchronizedEtsy',$attributes);
            if($value) {
                foreach ($product_ids as $item) {
                    $this->logUpdate(null, $item, $attributes);
                }
            }
        } catch (Exception $e){
            Mage::logException($e);
            return;
        }
    }

}