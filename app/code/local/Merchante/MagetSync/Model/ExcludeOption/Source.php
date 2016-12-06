<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_LanguageEtsy_Source
 */
class Merchante_MagetSync_Model_ExcludeOption_Source
{
    /**
     * Method for loading exclusion option values
     * in system's drop down
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=> false,'label' => 'No'),
            array('value' => true, 'label' => 'Yes')
        );
    }

}