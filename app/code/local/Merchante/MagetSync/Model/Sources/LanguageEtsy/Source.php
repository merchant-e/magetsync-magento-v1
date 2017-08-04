<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_LanguageEtsy_Source
 */
class Merchante_MagetSync_Model_Sources_LanguageEtsy_Source
{
    /**
     * Method for loading languages values
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
                'value' => 'en',
                'label' => 'English'
            ),
            array(
                'value' => 'es',
                'label' => 'Spanish'
            ),
            array(
                'value' => 'de',
                'label' => 'German'
            ),
            array(
                'value' => 'fr',
                'label' => 'French'
            ),
            array(
                'value' => 'it',
                'label' => 'Italian'
            ),
            array(
                'value' => 'ja',
                'label' => 'Japanese'
            ),
            array(
                'value' => 'nl',
                'label' => 'Dutch'
            ),
            array(
                'value' => 'pt',
                'label' => 'Portuguese'
            ),
            array(
                'value' => 'ru',
                'label' => 'Russian'
            ),
        );
    }

}