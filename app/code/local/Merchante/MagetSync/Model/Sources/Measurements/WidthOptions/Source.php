<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_Measurements_WidthOptions_Source
 */
class Merchante_MagetSync_Model_Sources_Measurements_WidthOptions_Source
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
                'value' => '338',
                'label' => 'Inches'
            ),
            array(
                'value' => '339',
                'label' => 'Centimeters'
            ),
            array(
                'value' => '340',
                'label' => 'Other'
            )
        );
    }

}