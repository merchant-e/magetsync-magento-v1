<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for override Order Grid behaviours
 * Class Merchante_MagetSync_Block_Adminhtml_Order_Grid
 */
class Merchante_MagetSync_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize order grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /**
         * Create select with sql join
         */
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $table = Mage::getSingleton('core/resource')->getTableName('magetsync/orderEtsy');
        $collection->getSelect()->joinLeft(array('orderetsy'=>$table),
        'orderetsy.order_id=main_table.entity_id',
        array('is_order_etsy'=>'is_order_etsy'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Method for adding columns to grid
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased from (store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
                'filter_index' => 'main_table.store_id'
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
            'filter_index' => 'main_table.created_at'
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Items Ordered'),
            'index'     => 'qty_ordered',
            'type'      => 'number',
            'total'     => 'sum'
        ));

        $this->addColumn('is_order_etsy', array(
            'header'    => Mage::helper('magetsync')->__('Source'),
            'index'     => 'is_order_etsy',
            'filter_index'=>'orderetsy.is_order_etsy',
            'column_css_class' => 'magetsyncIsOrderEtsy',
            'renderer' => 'Merchante_MagetSync_Block_Adminhtml_Template_Grid_Renderer_Image',
            'type'      => 'checkbox',
            'filter_condition_callback' => array($this, '_filter_is_etsy'),
            'editable' => 'false'
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        return $this;
    }


    /**
     * Method for enabling filter with 'is_etsy' column
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _filter_is_etsy($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if($value == 1)
        {
           $this->getCollection()->addFieldToFilter('orderetsy.is_order_etsy',  array('neq' => 'NULL' ));
        }
        if($value == 0)
        {
           $this->getCollection()->addFieldToFilter('orderetsy.is_order_etsy', array('null' => true)); 
        }

        return $this;
    }

    /**
     * Method for adding aditional actions to grid
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                'label'=> Mage::helper('sales')->__('Cancel'),
                'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                'label'=> Mage::helper('sales')->__('Hold'),
                'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                'label'=> Mage::helper('sales')->__('Unhold'),
                'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
            'label'=> Mage::helper('sales')->__('Print Invoices'),
            'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
            'label'=> Mage::helper('sales')->__('Print Packingslips'),
            'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
            'label'=> Mage::helper('sales')->__('Print Credit Memos'),
            'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
            'label'=> Mage::helper('sales')->__('Print All'),
            'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        return $this;
    }

    /**
     * Method for returning edit information url
     * @param $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}