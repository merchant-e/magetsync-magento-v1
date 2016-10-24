<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
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
            ->_addBreadcrumb('MagetSync Manager','MagetSync Manager');
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
        $attributeTemplateId = $this->getRequest()->getParam('id');
        $attributeTemplateIdModel = Mage::getModel('magetsync/attributeTemplate')->load($attributeTemplateId);
        if ($attributeTemplateIdModel->getId() <> null || $attributeTemplateId == null)
        {
            Mage::register('magetsync_data', $attributeTemplateIdModel);
            $this->loadLayout();
            $this->_setActiveMenu('magetsync/attributeTemplate');
            $this->_addBreadcrumb('MagetSync Manager','MagetSync Manager');
            $this->renderLayout();
        }
        else
        {
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
        $attributeTemplateId = $this->getRequest()->getParam('id');
        $attributeTemplateIdModel = Mage::getModel('magetsync/attributeTemplate')->load($attributeTemplateId);
        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                $data['title'] = $this->composeTemplateTitle($data);
                if ($productIds = $this->getRequest()->getParam('in_products', null)) {
                    $productIdsToAddArr = Mage::helper('adminhtml/js')->decodeGridSerializedInput($productIds);
                    $data['product_ids'] = implode(',', $productIdsToAddArr);
                    $data['products_count'] = count($productIdsToAddArr);
                }
                $data['price'] = '';
                $attributeTemplateIdModel->addData($data);
                $attributeTemplateIdModel->save();

                $this->_getSession()->addSuccess($this->__('The attribute template has been saved.'));
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
        $categoryModel = Mage::getModel('magetsync/category')->load($data['category_id']);
        $title .= $categoryModel->getShortName();

        if ($subcatId = $data['subcategory_id']) {
            $title .= '>' . $categoryModel->load($subcatId)->getShortName();
        }
        if ($subsubcatId = $data['subsubcategory_id']) {
            $title .= '>' . $categoryModel->load($subsubcatId)->getShortName();
        }
        $title .= ' | ';

        $shopSectionColl = Mage::getModel('magetsync/shopSection')->getCollection();
        $query = $shopSectionColl->getSelect()->where('shop_section_id= ?',$data['shop_section_id']);
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        $title .= $query[0]['title'] . ' | ';

        $shippingTemplateColl = Mage::getModel('magetsync/shippingTemplate')->getCollection();
        $query = $shippingTemplateColl->getSelect()->where('shipping_template_id= ?',$data['shipping_template_id']);
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        $title .= $query[0]['title'];

        return $title;
    }
}
