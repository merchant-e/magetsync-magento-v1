<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for handling catalog product actions
 * Class Merchante_MagetSync_Adminhtml_Catalog_Product_IndexController
 */
class Merchante_MagetSync_Adminhtml_Catalog_Product_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Method for action 'queue to Etsy'
     */
    public function indexAction()
    {
        try
        {
        $data = $this->getRequest()->getPost();
        $productIds = (isset($data['product'])?$data['product']:null);
            if(is_array($productIds))
            {
                $products = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*')->addIdFilter($productIds)->load();
                foreach($products as $product)
                {
                    $data = $product->getData();

                    if($data['visibility'] != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                        $parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($data['entity_id']);
                        if (!$parent) {
                            if ($data['synchronizedEtsy'] <> 1) {
                                $result = Mage::getModel('magetsync/listing')->saveListingSynchronized($product,null,true);
                                if ($result && $result['success']) {
                                    $product->setData('synchronizedEtsy', 1)->getResource()->saveAttribute($product, 'synchronizedEtsy');
                                    Mage::getSingleton('adminhtml/session')
                                        ->addSuccess(Mage::helper('magetsync')->__('Successfully saved [' . $data['entity_id'] . '].'));
                                } else {
                                    Mage::getSingleton('adminhtml/session')
                                        ->addError(Mage::helper('magetsync')->__($result['error'] . ' [' . $data['entity_id'] . ']'));
                                }
                            } else {
                                Mage::getSingleton('adminhtml/session')
                                    ->addError(Mage::helper('magetsync')->__('Already in queue [' . $data['entity_id'] . '].'));
                            }
                        } else {
                            Mage::getSingleton('adminhtml/session')
                                ->addError(Mage::helper('magetsync')->__('This is a child product, you can not synchronize this kind of product [' . $data['entity_id'] . '].'));
                        }
                    }else{
                        Mage::getSingleton('adminhtml/session')
                            ->addError(Mage::helper('magetsync')->__('This a product \'Not Visible Individually\', you can not synchronize this kind of product   [' . $data['entity_id'] . '].'));
                    }
                }
            }
            Mage::getSingleton('adminhtml/session')
                ->settestData(false);
            $this->_redirect('adminhtml/catalog_product/index');
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
            Mage::log("Error: ".print_r($e, true),null,'magetsync_product.log');
            Mage::getSingleton('adminhtml/session')
                ->settestData($this->getRequest()
                        ->getPost()
                );
            $this->_redirect('adminhtml/catalog_product/index');
            return;
        }
    }
}