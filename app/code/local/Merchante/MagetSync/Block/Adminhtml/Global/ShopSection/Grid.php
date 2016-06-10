<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for creating Shipping Template Grid
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Grid
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Intialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('shopSectionGrid');
        $this->setDefaultSort('shop_section_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Method for adding information to grid
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('magetsync/shopSection');
        $this->setCollection($collection->getCollection());
        return parent::_prepareCollection();
    }

    /**
     * Method for adding columns to grid
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('shop_section_id',
            array(
                'header' => 'ID',
                'align' =>'right',
                'width' => '80px',
                'index' => 'shop_section_id',
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