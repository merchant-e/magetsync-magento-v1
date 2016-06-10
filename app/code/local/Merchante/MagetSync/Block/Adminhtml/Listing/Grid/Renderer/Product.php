<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for rendering order grid
 * Class Merchante_MagetSync_Block_Adminhtml_Template_Grid_Renderer_Product
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Grid_Renderer_Product extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Override render method
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    /**
     * Method for getting row's value and set status image
     * @param Varien_Object $row
     * @return string
     */
    protected function _getValue(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
            $product = Mage::getModel('catalog/product')->load($val);
            if ($product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $childProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getChildrenIds($val);
                $simpleProducts = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*')
                    ->addIdFilter($childProducts)->load();

                //$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                //$simpleProducts = $conf->getUsedProductCollection()->addAttributeToSelect(array('sku', 'name'), 'inner')->addFilterByRequiredOptions()->load();
                //$simpleProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                if ($simpleProducts) {
                    $out = '<h3><b>('.$product->getData('sku').')</b> ' . $product->getData('name') . '</h3>';
                    $out = $out.'<ul class="lmTable">
                                    <div class="lmHeader">
                                        <li class="lmRow">
                                            <span class="lmID">ID</span>
                                            <span class="lmSKU">SKU</span>
                                            <span>Name</span>
                                            <span class="lmQty">Quantity</span>
                                        </li>
                                    </div>
                                    <div class="lmBody">';
                    foreach ($simpleProducts as $simple) {
                        $simpleData = $simple->getData();
                        $stockItem = Mage::getModel('cataloginventory/stock_item')
                            ->loadByProduct($simpleData['entity_id']);
                        $stockData = $stockItem->getData();
                        if ($stockData && array_key_exists('is_in_stock',$stockData) && $stockData['is_in_stock']) {
                            $qty = array_key_exists('qty',$stockData)?$stockData['qty']:0;
                        } else {
                            $qty = 0;
                        }
                        $out = $out.'<li class="lmRow"><span class="lmID">' . $simpleData['entity_id'] . '</span><span class="lmSKU">' . $simpleData['sku'] . '</span><span>' . $simpleData['name'] . '</span><span class="lmQty">' .round($qty, 2) . '</span></li>';
                    }
                    $out = $out.'</div></ul>';
                } else {
                    $out = $row['title'];
                }
            } else {
                $out = '<b>('.$product->getData('sku').')</b> '.$row['title'];
            }

        return $out;
    }

}