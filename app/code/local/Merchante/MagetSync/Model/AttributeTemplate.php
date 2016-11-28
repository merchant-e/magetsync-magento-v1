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

    /**
     * @return array
     */
    public function toPricingRuleOptionArray()
    {
        $returnArr = [];
        $returnArr[] = array('value' => 'original', 'label' => Mage::helper('magetsync')->__('Original Price'));
        $returnArr[] = array('value' => 'increase', 'label' => Mage::helper('magetsync')->__('Increase'));
        $returnArr[] = array('value' => 'decrease', 'label' => Mage::helper('magetsync')->__('Decrease'));

        return $returnArr;
    }

    /**
     * @return array
     */
    public function toPricingStrategyOptionArray()
    {
        $returnArr = [];
        $returnArr[] = array('value' => 'fixed', 'label' => Mage::helper('magetsync')->__('Fixed'));
        $returnArr[] = array('value' => 'percentage', 'label' => Mage::helper('magetsync')->__('%'));

        return $returnArr;
    }
}