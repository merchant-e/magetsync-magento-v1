<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_Measurements_DiameterOptions_Source
 */
class Merchante_MagetSync_Model_Sources_Measurements_DiameterOptions_Source
{
    /**
     * Method for loading scale values
     * in system's drop down
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => null,
                'label' => Mage::helper('magetsync')->__('Please Select')
            ),
            array(
                'value' => '341',
                'label' => 'Inches'
            ),
            array(
                'value' => '342',
                'label' => 'Centimeters'
            ),
            array(
                'value' => '343',
                'label' => 'Other'
            )
        );
    }

}