<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * Class Merchante_MagetSync_Model_Source_Option
 */
class Merchante_MagetSync_Model_Sources_Source_Option extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions($withEmpty = true)
    {
        $options = array();
        $occasionModel = Mage::getModel('magetsync/occasion')->getCollection();
        foreach ($occasionModel as $value) {
            $options[] = array(
                'label' => $value['name'],
                'value' => $value['display']
            );
        }
        if ($withEmpty) {
            array_unshift(
                $options, array(
                'label' => 'Select occasion',
                'value' => null
            )
            );
        }

        return $options;
    }
}
