<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 * Class Merchante_MagetSync_Adminhtml_Magetsync_AttributTemplatesController
 */
class Merchante_MagetSync_Adminhtml_Magetsync_AttributeTemplateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Method for renderLayout
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('magetsync/attributeTemplate')
             ->_addBreadcrumb('MagetSync Manager', 'MagetSync Manager');

        return $this;
    }

    /**
     * Index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Method edit for listing
     */
    public function editAction()
    {
        $templateToDuplicateId = $this->getRequest()->getParam('templateToDuplicateId');
        $attributeTemplateId = $this->getRequest()->getParam('id');
        if ($templateToDuplicateId) {
            $templateDuplicateFromModelData =
                Mage::getModel('magetsync/attributeTemplate')->load($templateToDuplicateId)->getData();
            $attributeTemplateIdModel =
                Mage::getModel('magetsync/attributeTemplate')->setData($templateDuplicateFromModelData);
        } else {
            $attributeTemplateIdModel = Mage::getModel('magetsync/attributeTemplate')->load($attributeTemplateId);
        }
        if ($attributeTemplateIdModel->getId() <> null || $attributeTemplateId == null) {
            Mage::register('magetsync_data', $attributeTemplateIdModel);
            $this->loadLayout();
            $this->_setActiveMenu('magetsync/attributeTemplate');
            $this->_addBreadcrumb('MagetSync Manager', 'MagetSync Manager');
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('magetsync')->__("Attribute Template does not exist"));
            $this->_redirect('*/*/');
        }

    }

    /**
     * Create template
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save attribute template action
     */
    public function saveAction()
    {
        $saveAndQueue = $this->getRequest()->getParam('queueListings');
        $attributeTemplateId = $this->getRequest()->getParam('id');
        $attributeTemplateModel = Mage::getModel('magetsync/attributeTemplate')->load($attributeTemplateId);
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                $postData['title'] = $this->composeTemplateTitle($postData);
                $productIdsToAddArr = array();
                if ($this->getRequest()->getParam('in_products', null) !== null) {
                    $productIds = $this->getRequest()->getParam('in_products', null);
                    if ($productIds) {
                        $productIdsToAddArr = Mage::helper('adminhtml/js')->decodeGridSerializedInput($productIds);
                    }
                } else {
                    if ($prodIdsArr = $attributeTemplateModel->getProductIds()) {
                        $productIdsToAddArr = explode(',', $prodIdsArr);
                    }
                }
                $postData['product_ids'] = implode(',', $productIdsToAddArr);
                $postData['products_count'] = count($productIdsToAddArr);

                if ($postData['pricing_rule'] == 'original' || $postData['affect_value'] == 0) {
                    $postData['pricing_rule'] = 'original';
                    $postData['affect_value'] = 0;
                    $postData['affect_strategy'] = NULL;
                }
                $origData = $attributeTemplateModel->getOrigData();
                $attributeTemplateModel->addData($postData);
                $attributeTemplateModel->save();
                $attributeTemplateId = $attributeTemplateModel->getId();

                if ($saveAndQueue) {
                    //Save products that were added ONLY
                    if ($attributeTemplateModel->dataHasChangedFor('product_ids')) {
                        $newData = $attributeTemplateModel->getData();
                        $updateNewProductsOnly = $this->compareTemplateDatas($newData, $origData);
                        if ($updateNewProductsOnly) {
                            $productIdsToAddArr =
                                array_diff($productIdsToAddArr, explode(',', $origData['product_ids']));
                        }
                    }
                    $products =
                        Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*')->addIdFilter(
                            $productIdsToAddArr
                        )->load();
                    $createdListingsData = array();
                    foreach ($products as $product) {
                        $parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild(
                            $product->getEntityId()
                        );
                        if (!$parent) {
                            $result =
                                Mage::getModel('magetsync/listing')->saveListingSynchronized($product, null, false);
                            if ($result && $result['success']) {
                                $product->setData('synchronizedEtsy', 1)->getResource()->saveAttribute(
                                    $product, 'synchronizedEtsy'
                                );
                                $createdListingsData[$product->getId()] = round($product->getPrice(), 2);
                            } else {
                                Mage::getSingleton('adminhtml/session')
                                    ->addError(
                                        Mage::helper('magetsync')->__(
                                            $result['error'] . ' [' . $product->getEntityId() . ']'
                                        )
                                    );
                            }
                        } else {
                            Mage::getSingleton('adminhtml/session')
                                ->addError(
                                    Mage::helper('magetsync')->__(
                                        'This is a child product, you can not synchronize this kind of product [' .
                                        $product->getEntityId() . '].'
                                    )
                                );
                        }
                    }

                    if (!$this->verifyEtsyApi() ||
                        Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_language') === null
                    ) {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('magetsync')->__('Please set up Etsy.')
                        );

                        return;
                    }

                    $listingModel = Mage::getModel('magetsync/listing');
                    $newListings = $listingModel->getCollection()->addFieldToSelect('*')->addFieldToFilter(
                        'idproduct', array('in' => array_keys($createdListingsData))
                    )->load();
                    foreach ($newListings as $listing) {
                        $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE;
                        $postData['sync_ready'] = 1;
                        $postData['title'] = $listing->getTitle();

                        if ($listing->getAttributeTemplateId() &&
                            $listing->getAttributeTemplateId() != $attributeTemplateId
                        ) {
                            $attributeTemplateModel->removeAssociatedProduct(
                                $listing->getAttributeTemplateId(), $listing->getIdproduct()
                            );
                        }
                        $postData['attribute_template_id'] = $attributeTemplateId;
                        $productPrice = $createdListingsData[$listing->getIdproduct()];

                        if ($postData['pricing_rule'] == 'original') {
                            $finalPrice = $productPrice;
                            $postData['is_custom_price'] = 0;
                        } else {
                            if ($postData['affect_strategy'] == 'percentage') {
                                $delta = round($productPrice * (floatval($postData['affect_value']) / 100), 2);
                            } else {
                                $delta = $postData['affect_value'];
                            }
                            if ($postData['pricing_rule'] == 'increase') {
                                $finalPrice = $productPrice + $delta;
                            } else {
                                $finalPrice = $productPrice - $delta;
                            }
                            $postData['is_custom_price'] = 1;
                        }
                        $postData['price'] = $finalPrice;

                        $listing->addData($postData);
                        $listing->save();
                    }
                }

                $this->_getSession()->addSuccess(
                    $this->__('The attribute template has been saved and listing(s) auto queued.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }


    /**
     * Duplicate attribute template action
     */
    public function duplicateAction()
    {
        if ($templateId = $this->getRequest()->getParam('templateToDuplicateId')) {
            $this->_forward('edit', null, null, array('templateToDuplicateId' => $templateId));
        } else {
            $this->_getSession()->addError('Unable to duplicate.');
            $this->_redirect('*/*/');
        }

    }


    /**
     * Delete attribute template action
     */
    public function deleteAction()
    {
        if ($templateId = $this->getRequest()->getParam('id')) {
            $attributeTemplateModel = Mage::getModel('magetsync/attributeTemplate')->load($templateId);
            try {
                $attributeTemplateModel->delete();
                $this->_getSession()->addSuccess($this->__('The template has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }

    /**
     * Mass template deletion
     */
    public function massDeleteAction()
    {
        $templateIds = $this->getRequest()->getParam('template_ids');
        if (!is_array($templateIds)) {
            $this->_getSession()->addError($this->__('Please select templates(s).'));
        } else {
            if (!empty($templateIds)) {
                try {
                    foreach ($templateIds as $templateId) {
                        $attributeTemplateModel = Mage::getModel('magetsync/attributeTemplate')->load($templateId);
                        $attributeTemplateModel->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($templateIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Grid serializer call
     */
    public function productsGridAction()
    {
        $this->loadLayout()
             ->getLayout()
             ->getBlock('magetsync.attributetemplate.edit.tab.products')
             ->setSelectedProducts($this->getRequest()->getPost('products', null));

        $this->renderLayout();
    }

    /**
     * Products tab ajax load
     */
    public function productsTabAction()
    {
        $this->loadLayout()
             ->getLayout()
             ->getBlock('magetsync.attributetemplate.edit.tab.products');

        $this->renderLayout();
    }

    /**
     * Auto generated title for template
     * @param $data
     * @return string
     */
    public function composeTemplateTitle($data)
    {
        $title = '';
        $shortName = $this->getCategoryShortName($data['category_id']);
        $title .= $shortName;

        if ($subcatId = $data['subcategory_id']) {
            $shortName = $this->getCategoryShortName($subcatId);
            $title .= ' > ' . $shortName;
        }
        if ($subsubcatId = $data['subsubcategory_id']) {
            $shortName = $this->getCategoryShortName($subsubcatId);
            $title .= ' > ' . $shortName;
        }
        if ($subcat4Id = $data['subcategory4_id']) {
            $shortName = $this->getCategoryShortName($subcat4Id);
            $title .= ' > ' . $shortName;
        }
        if ($subcat5Id = $data['subcategory4_id']) {
            $shortName = $this->getCategoryShortName($subcat5Id);
            $title .= ' > ' . $shortName;
        }
        if ($subcat6Id = $data['subcategory4_id']) {
            $shortName = $this->getCategoryShortName($subcat6Id);
            $title .= ' > ' . $shortName;
        }
        $title .= ' | ';

        $shopSectionColl = Mage::getModel('magetsync/shopSection')->getCollection();
        $query = $shopSectionColl->getSelect()->where('shop_section_id= ?', $data['shop_section_id']);
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        $title .= $query[0]['title'] . ' | ';

        $shippingTemplateColl = Mage::getModel('magetsync/shippingTemplate')->getCollection();
        $query = $shippingTemplateColl->getSelect()->where('shipping_template_id= ?', $data['shipping_template_id']);
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        $title .= $query[0]['title'];

        return $title;
    }

    /**
     * @param $levelId
     * @return mixed
     */
    public function getCategoryShortName($levelId)
    {
        $categoryColl = Mage::getModel('magetsync/category')->getCollection();
        $query = $categoryColl->getSelect()->where('level_id= ?', $levelId);
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);

        return $query[0]['short_name'];
    }

    /**
     * Compare data from DB and new data to save
     * @param $newData
     * @param $origData
     * @return bool
     */
    public function compareTemplateDatas($newData, $origData)
    {
        foreach ($origData as $dataKey => $dataVal) {
            if ($dataKey == 'product_ids' || $dataKey == 'products_count') {
                continue;
            }

            if ($newData[$dataKey] == $dataVal) {
                continue;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Method for verifying if the api connection is correct
     * @return bool
     */
    public function verifyEtsyApi()
    {
        if (Mage::getModel('magetsync/etsy')->load(1)->getData('AccessToken') <> '') {
            return true;
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('magetsync')->__(
                        'First you must authorise access to Etsy under System > Configuration > MagetSync'
                    )
                );
            $this->_redirect('*/*/');

            return false;
        }
    }

    /**
     * Check if user has permissions to visit page
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/magetsync/attributeTemplate');
    }
}
