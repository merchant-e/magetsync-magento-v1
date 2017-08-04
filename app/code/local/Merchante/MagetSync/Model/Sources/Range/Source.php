<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 * Class Merchante_MagetSync_Model_Sources_Range_Source
 */
class Merchante_MagetSync_Model_Sources_Range_Source
{
    /**
     * Method for loading exclusion option values
     * in system's drop down
     * @return array
     */
    public function toOptionArray()
    {
        $returnArray = array();

        for ($i = 1; $i < 11; $i++) {
            $returnArray[] = array(
                'value' => $i,
                'label' => $i
            );
        }

        return $returnArray;
    }
}