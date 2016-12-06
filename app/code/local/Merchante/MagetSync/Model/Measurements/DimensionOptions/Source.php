<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Measurements_DimensionOptions_Source
 */
class Merchante_MagetSync_Model_Measurements_DimensionOptions_Source
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
            array('value' => '344', 'label' => 'Inches'),
            array('value' => '345', 'label' => 'Centimeters'),
            array('value' => '346', 'label' => 'Other')
        );
    }

}