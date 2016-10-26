<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class for creating template product grid
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tab_Products
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productsGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('in_products');
        if ($this->getSelectedProducts()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
        $this->setSaveParametersInSession(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('synchronizedEtsy')
            ->addAttributeToFilter('visibility', array(
                'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addAttributeToFilter('synchronizedEtsy', array('neq' => '1'), 'right')
            ->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable')));

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->_getSelectedProducts(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();
        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return mixed|string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/productsGrid', array('_current'=>true));
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    protected function _getSelectedProducts()
    {
        return $this->getRequest()->getPost('products', null) ? $this->getRequest()->getPost('products', null) : $this->getSelectedProducts();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getSelectedProducts()
    {
        $assigned_product_ids = array();
        $attributeTemplateId = $this->getRequest()->getParam('id');
        $attributeTemplateIdModel = Mage::getModel('magetsync/attributeTemplate')->load($attributeTemplateId);
        if ($attributeTemplateIdModel->getProductIds()) {
            $assigned_product_ids = explode(',', $attributeTemplateIdModel->getProductIds());
        }
        return $assigned_product_ids;
    }
}