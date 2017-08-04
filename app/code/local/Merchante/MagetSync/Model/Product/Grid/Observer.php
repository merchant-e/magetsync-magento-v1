<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Product_Grid_Observer
 */

class Merchante_MagetSync_Model_Product_Grid_Observer
{
    public function addMassAction($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'catalog_product')
        {
            $block->addItem(
                'magetsync',
                array('label' => Mage::helper('magetsync')->__("Queue to Etsy"),
                    'url'   => Mage::app()->getStore()->getUrl('adminhtml/catalog_product_index/index')
            ));
        }
    }
}