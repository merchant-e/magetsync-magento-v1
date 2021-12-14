<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_GlobalNote_Source
 */
class Merchante_MagetSync_Model_Sources_GlobalNote_Source
{
    /**
     * Method for loading product attributes
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->getItems();

        $returnArray = array(
            array(
            'value' => '',
            'label' => 'Please select'
        ));
        foreach ($attributes as $attribute){
            $returnArray[] = array(
                                'value' => $attribute->getAttributecode(),
                                'label' => $attribute->getFrontendLabel()
                             );
        }

        return $returnArray;
    }

}