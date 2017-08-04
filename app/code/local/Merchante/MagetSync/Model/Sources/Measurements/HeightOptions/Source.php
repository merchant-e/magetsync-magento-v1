<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_Measurements_HeightOptions_Source
 */
class Merchante_MagetSync_Model_Sources_Measurements_HeightOptions_Source
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
                'value' => '347',
                'label' => 'Inches'
            ),
            array(
                'value' => '348',
                'label' => 'Centimeters'
            ),
            array(
                'value' => '349',
                'label' => 'Other'
            )
        );
    }

}