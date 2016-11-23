<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class Merchante_MagetSync_Model_AttributeTemplate
 */
class Merchante_MagetSync_Model_AttributeTemplate extends Mage_Core_Model_Abstract
 {
    /**
     *
     */
    public function _construct()
   	{
        parent::_construct();
        $this->_init('magetsync/attributeTemplate');
   	}

    /**
     * Removes product by ID from attribute template associated products
     * @param $attributeTemplateId
     * @param $asociatedProductId
     */
    public function removeAssociatedProduct($attributeTemplateId, $asociatedProductId)
   	{
        $this->load($attributeTemplateId);
        $associatedProductsArr = explode(',', $this->getProductIds());
        $key = array_search($asociatedProductId, $associatedProductsArr);
        if (false !== $key) {
            unset($associatedProductsArr[$key]);
        }
        $this->setProductsCount(count($associatedProductsArr));
        $this->setProductIds(implode(',', $associatedProductsArr));
        $this->save();
   	}
}