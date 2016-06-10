<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Measurements_LengthOptions_Source
 */
class Merchante_MagetSync_Model_Measurements_LengthOptions_Source
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
            array('value' => '350', 'label' => 'Inches'),
            array('value' => '351', 'label' => 'Centimeters'),
            array('value' => '352', 'label' => 'Other')
        );
    }

}