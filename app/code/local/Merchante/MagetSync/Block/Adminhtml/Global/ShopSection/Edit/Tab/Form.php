<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for creating Shop Section form
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Edit_Tab_Form
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShopSection_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Method for creating form inputs
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('global_shopsection_form',
            array('legend'=> Mage::helper('magetsync')->__("Shop section information")));
        $fieldset->addField('shop_section_id', 'text',
            array(
                'label' => Mage::helper('magetsync')->__("ID Shop Section"),
                'required' => false,
                'class' =>'disabled',
                'readonly' => true,
                'name' => 'shop_section_id',
            ));

        $fieldset->addField('title', 'text',
            array(
                'label' => Mage::helper('magetsync')->__("Title"),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'title',
            ));

        if ( Mage::registry('magetsync_shopsection') )
        {
            $form->setValues(Mage::registry('magetsync_shopsection')->getData());
        }
        return parent::_prepareForm();
    }
}