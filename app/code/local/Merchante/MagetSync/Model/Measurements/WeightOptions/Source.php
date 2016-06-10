<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Measurements_WeightOptions_Source
 */
class Merchante_MagetSync_Model_Measurements_WeightOptions_Source
{
    /**
     * Method for loading scale values
     * in system's drop down
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=> null,'label' => Mage::helper('magetsync')->__('Please Select')),
            array('value' => '332', 'label' => 'Pounds'),
            array('value' => '331', 'label' => 'Ounces'),
            array('value' => '333', 'label' => 'Grams'),
            array('value' => '334', 'label' => 'Kilograms')
        );
    }

}