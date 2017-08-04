<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for creating button in system configuration
 * Class Merchante_MagetSync_Block_ButtonAuthorize
 */
class Merchante_MagetSync_Block_ButtonAuthorize extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Method for creating block with widget button
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $label = Mage::helper('magetsync')->__('Authorize');
        if (version_compare(phpversion(), '5.4.0', '<')===true) {
            $html = "<p style='color:red'>" . Mage::helper('magetsync')->__("You're using PHP version: " . phpversion()
                    . ". MagetSync requires 5.4 or newer. Update your PHP version before proceeding.") . "</p>";
            $html .= $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('disabled')
                ->setDisabled(true)
                ->setLabel($label)
                ->toHtml();

            return $html;
        }
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/magetsync_api/authorize');
        $etsyModel = Mage::getModel('magetsync/etsy');
        $configuration = $etsyModel->getConfiguration();
        $access_token = $configuration['AccessToken'];
        $access_token_secret = $configuration['AccessTokenSecret'];
        $class = '';
        $disabled = false;
        if($access_token != "" && $access_token_secret != "")
        {
            $class = 'greenAuthorize';
            $disabled = true;
            $label = Mage::helper('magetsync')->__('Authorized');

        }

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass($class)
            ->setDisabled($disabled)
            ->setLabel($label)
            ->setOnClick("launch()")
            ->toHtml();
            $html .= '<script type="text/javascript">function launch(){

            var language = document.getElementById(\'magetsync_section_magetsync_group_magetsync_field_language\').value;
            var shop = document.getElementById(\'magetsync_section_magetsync_group_magetsync_field_shop\').value;
            var token = document.getElementById(\'magetsync_section_magetsync_group_magetsync_field_tokencustomer\').value;
            if (configForm.validator && configForm.validator.validate())
            {
                $(\'config_edit_form\').request({method: \'post\',
                onSuccess: function(value){
                   setLocation(\''.$url.'\');
                },
                onFailure: function(){  }});
            }else
            {
              configForm.submit();
            }
            }
            </script>';

        return $html;
    }
}