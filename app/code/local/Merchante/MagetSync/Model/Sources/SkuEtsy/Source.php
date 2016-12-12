<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_SkuEtsy_Source
 */
class Merchante_MagetSync_Model_Sources_SkuEtsy_Source
{
    /**
     * Method for loading exclusion option values
     * in system's drop down
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '',
                'label' => 'None'
            ),
            array(
                'value' => 'alphanumeric',
                'label' => 'Alpha Numeric'
            ),
            array(
                'value' => 'alphabets',
                'label' => 'Alphabets'
            ),
            array(
                'value' => 'numeric',
                'label' => 'Numeric'
            ),
        );
    }

}