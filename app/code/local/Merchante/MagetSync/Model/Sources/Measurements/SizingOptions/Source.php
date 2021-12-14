<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_Measurements_SizingOptions_Source
 */
class Merchante_MagetSync_Model_Sources_Measurements_SizingOptions_Source
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
                'value' => '301',
                'label' => 'Alpha'
            ),
            array(
                'value' => '327',
                'label' => 'Inches'
            ),
            array(
                'value' => '328',
                'label' => 'Centimeters'
            ),
            array(
                'value' => '335',
                'label' => 'Fluid Ounces'
            ),
            array(
                'value' => '336',
                'label' => 'Millilitres'
            ),
            array(
                'value' => '337',
                'label' => 'Litres'
            ),
            array(
                'value' => '329',
                'label' => 'Other'
            )
        );
    }

}