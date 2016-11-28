<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for creating Listing Grid
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Grid
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('listing_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->unsetChild('search_button');
    }

    /**
     * Method for adding information to grid
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('magetsync/listing')->getCollection()->addFilter('enabled',Merchante_MagetSync_Model_Listing::LISTING_ENABLED);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Method for adding columns to grid
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('idproduct',
        array(
            'header' => 'ID',
            'align' =>'right',
            'width' => '50px',
            'index' => 'idproduct',
        ));

       /* $this->addColumn('sku',
            array(
                'header' => 'SKU',
                'align' =>'right',
                'width' => '50px',
                'index' => 'sku',
            ));*/

        $this->addColumn('title',
            array(
                'header' => Mage::helper('magetsync')->__('Title'),
                'align' =>'left',
                'renderer' => 'Merchante_MagetSync_Block_Adminhtml_Listing_Grid_Renderer_Product',
                'index' => 'idproduct',
                'filter_condition_callback' => array($this, '_titleFilter')
            ));

//        $this->addColumn('description', array(
//            'header' => Mage::helper('magetsync')->__('Description'),
//            'align' =>'left',
//            'index' => 'description',
//        ));

        $this->addColumn('quantity', array(
            'header' => Mage::helper('magetsync')->__('Quantity'),
            'align' =>'right',
            'width' => '50px',
            'index' => 'quantity',
        ));

        $this->addColumn('sync', array(
            'header'    => Mage::helper('magetsync')->__('Status'),
            'index'     => 'sync',
            'width' => '50px',
            'filter_index'=>'sync',
            'renderer' => 'Merchante_MagetSync_Block_Adminhtml_Listing_Grid_Renderer_Image',
            'editable' => 'false',
            'type' => 'options',
            'options' => Merchante_MagetSync_Block_Adminhtml_Listing_Grid_Renderer_Image::getStatesArray()

        ));

        return parent::_prepareColumns();
    }

    /**
     * Filter title function
     * @return $this
     */
    protected function _titleFilter($collection, $column) {
        $filterTitle = $column->getFilter()->getValue();
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        //$this->getCollection()->addFieldToFilter('title', array('eq' => $filterTitle));
        $this->getCollection()->getSelect()
            ->joinInner( array('products'=> Mage::getSingleton('core/resource')->getTableName('catalog/product')),
                'main_table.idproduct = products.entity_id',
                array())
            ->where(
            "title like ? OR products.sku like ?"
            , "%$filterTitle%", "%$filterTitle%");

        return $this;
    }

    /**
     * Override method for adding more actions
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('listingids');
        $this->getMassactionBlock()->setFormFieldName('listingids');

        $this->getMassactionBlock()->addItem('updateattributes', array(
            'label'=> Mage::helper('magetsync')->__('Update attributes'),
            'url'  => $this->getUrl('*/*/updateattributes', array('' => ''))
            //'confirm' => Mage::helper('magetsync')->__('Are you sure?'),
        ));

        $this->getMassactionBlock()->addItem('sendtoetsy', array(
            'label'=> Mage::helper('magetsync')->__('Send to Etsy'),
            'url'  => $this->getUrl('*/*/sendtoetsy', array('' => '')),
            'confirm' => Mage::helper('magetsync')->__('You are about to list product(s) on Etsy. If you have filled out all the required information please proceed.'),
        ));

        $this->getMassactionBlock()->addItem('queuelistings', array(
            'label'=> Mage::helper('magetsync')->__('Queue listings'),
            'url'  => $this->getUrl('*/*/queuelistings', array('' => '')),
            'confirm' => Mage::helper('magetsync')->__('You are about to queue product(s) on Etsy. If you have filled out all the required information please proceed.'),
        ));

        // reset and re-synchronize images on Etsy
        $this->getMassactionBlock()->addItem('resetimages', array(
            'label'=> Mage::helper('magetsync')->__('Reset Images'),
            'url'  => $this->getUrl('*/*/resetimages', array('' => '')),
            'confirm' => Mage::helper('magetsync')->__('You are about to re-upload the images on Etsy.'),
        ));

	$this->getMassactionBlock()->addItem('deleteoption', array('label'=> Mage::helper('magetsync')->__('Delete'),'url'  => $this->getUrl('*/*/deleteoption'),'confirm' => Mage::helper('magetsync')->__('Are you sure?'),));
        return $this;
    }

    /**
     * Method for returning edit information url
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        //When status is force_delete
        if($row->getData('sync') == 7)
        {
           return false;
        }else {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }
    }
}