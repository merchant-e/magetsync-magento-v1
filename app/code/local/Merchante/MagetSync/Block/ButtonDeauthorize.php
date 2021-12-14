<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for creating button in system configuration
 * Class Merchante_MagetSync_Block_ButtonDeauthorize
 */
class Merchante_MagetSync_Block_ButtonDeauthorize extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Method for creating block with widget button
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/magetsync_api/deauthorize');

        $etsyModel = Mage::getModel('magetsync/etsy');
        $configuration = $etsyModel->getConfiguration();
        $access_token = $configuration['AccessToken'];
        $access_token_secret = $configuration['AccessTokenSecret'];
        //$disabled = true;
        if($access_token != "" && $access_token_secret != "") {
            $disabled = false;
            $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('scalable')
                ->setDisabled($disabled)
                ->setLabel(Mage::helper('magetsync')->__('Deauthorize'))
                ->setOnClick("startDeauthorize('$url')")
                ->toHtml();
            $html .= '<script type="text/javascript">function startDeauthorize(){
            if (confirm(\'Are you sure you wish to deauthorize this store?\')) {
                if (configForm.validator && configForm.validator.validate())
                {
                    $(\'config_edit_form\').request({method: \'post\',
                    onSuccess: function(value){
                       setLocation(\'' . $url . '\');
                    },
                    onFailure: function(){  }});
                }else
                {
                  configForm.submit();
                }
                }
            }
            </script>';
        }else{
            $html = '';
        }
        return $html;
    }
}
?>