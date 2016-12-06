<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for creating button in system configuration
 * Class Merchante_MagetSync_Block_ButtonDeleteListings
 */
class Merchante_MagetSync_Block_ButtonDeleteResizeListings extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Method for creating block with widget button
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/magetsync_index/deleteresizeimages');
        $label = Mage::helper('magetsync')->__('Delete Resized Images');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($label)
            ->setOnClick("deleteResizeListingImages()")
            ->toHtml();
        $html .= '<script type="text/javascript">function deleteResizeListingImages(){
         if (confirm("Are you sure you want to delete the resized images?")) {
                    new Ajax.Request(\''.$url.'\', {
                    method: \'get\',
                    onLoading: function (response) {
                    },
                    onComplete: function(response) {
                        var dataJson = response.responseText.evalJSON();
                        if(dataJson.success == true) {
                            alert(dataJson.msg);
                        } else {
                            alert(dataJson.msg);
                        }
                    }
                });
            }
        }
        </script>';
        return $html;
    }
}
?>