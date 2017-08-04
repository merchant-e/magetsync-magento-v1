<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Order_Grid_Observer
 */

class Merchante_MagetSync_Model_Order_Grid_Observer
{
    public function beforeBlockToHtml(Varien_Event_Observer $observer)
    {
        $use = Mage::getStoreConfig('magetsync_section/magetsync_group_sales_order/magetsync_field_column_order');
        if($use) {
            $grid = $observer->getBlock();
            /**
             * Mage_Adminhtml_Block_Sales_Order_Grid
             */
            if ($grid instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
                $grid->addColumnAfter('is_order_etsy', array(
                        'header' => Mage::helper('magetsync')->__('Source'),
                        'index' => 'is_order_etsy',
                        'filter_index' => 'orderetsy.is_order_etsy',
                        'column_css_class' => 'magetsyncIsOrderEtsy',
                        'renderer' => 'Merchante_MagetSync_Block_Adminhtml_Template_Grid_Renderer_Image',
                        'filter_condition_callback' => array($this, '_filter_is_etsy'),
                        'type' => 'options',
                        'editable' => 'false',
                        'options' => Merchante_MagetSync_Model_Order_Grid_Observer::getOptionsArray()
                    ),
                    'shipping_name'
                );

                $grid->sortColumnsByOrder();
            }
        }
    }

    public static function getOptionsArray()
    {
        $options = array();
        $options['1'] = Mage::helper('magetsync')->__('Etsy');
        $options['0'] = Mage::helper('magetsync')->__('Magento');
        return $options;
    }

    /**
     * Method for enabling filter with 'is_etsy' column
     * @param $collection
     * @param $column
     * @return $this
     */
    public function _filter_is_etsy($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if($value == 1)
        {
            $collection->addFieldToFilter('orderetsy.is_order_etsy',  array('neq' => 'NULL' ));
        }
        if($value == 0)
        {
            $collection->addFieldToFilter('orderetsy.is_order_etsy', array('null' => true));
        }
        return $this;
    }

    /**
     * Moved to block class
     * @param Varien_Event_Observer $observer
     */
    public function collectionLoadBefore(Varien_Event_Observer $observer)
    {
        $use = Mage::getStoreConfig('magetsync_section/magetsync_group_sales_order/magetsync_field_column_order');
        if($use) {
            $collection = $observer->getOrderGridCollection();
            $table = Mage::getSingleton('core/resource')->getTableName('magetsync/orderEtsy');
            $collection->getSelect()->joinLeft(array('orderetsy' => $table),
                'orderetsy.order_id=main_table.entity_id',
                array('is_order_etsy' => 'is_order_etsy'));
        }

    }

  }