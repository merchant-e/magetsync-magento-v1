<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for creating Shipping Template Grid
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Grid
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('shippingTemplateGrid');
        $this->setDefaultSort('shipping_template_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Method for adding information to grid
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('magetsync/shippingTemplate')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Method for adding columns to grid
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('shipping_template_id',
            array(
                'header' => 'ID',
                'align' =>'right',
                'width' => '50px',
                'index' => 'shipping_template_id',
                'filter_index' => 'shipping_template_id'
            ));

        $this->addColumn('title',
            array(
                'header' => Mage::helper('magetsync')->__('Title'),
                'align' =>'left',
                'index' => 'title',
            ));
        return parent::_prepareColumns();
    }

    /**
     * Method for returning edit information url
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}