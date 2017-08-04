<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for creating templates Grid
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Grid
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('templateGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Method for adding information to grid
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('magetsync/attributeTemplate')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Method for adding columns to grid
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id',
        array(
            'header' => 'ID',
            'align' =>'right',
            'width' => '150px',
            'index' => 'id',
        ));

        $this->addColumn('title',
            array(
                'header' => Mage::helper('magetsync')->__('Title'),
                'align' =>'left',
                'index' => 'title'
            ));

        $this->addColumn('products_count', array(
            'header' => Mage::helper('magetsync')->__('Number of products'),
            'align' =>'right',
            'width' => '50px',
            'index' => 'products_count'
        ));

        return parent::_prepareColumns();
    }


    /**
     * Override method for adding more actions
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('template_ids');
        $this->getMassactionBlock()->setFormFieldName('template_ids');

        $this->getMassactionBlock()->addItem('massdelete', array(
            'label'=> Mage::helper('magetsync')->__('Delete templates'),
            'url'  => $this->getUrl('*/*/massdelete', array('' => '')),
            'confirm' => Mage::helper('magetsync')->__('Are you sure?')
        ));

        return $this;
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