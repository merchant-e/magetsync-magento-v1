<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for creating button in system configuration
 * Class Merchante_MagetSync_Block_ButtonDeleteListings
 */
class Merchante_MagetSync_Block_ButtonDeleteListings extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Method for creating block with widget button
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/magetsync_index/deleteAllListings');
        $label = Mage::helper('magetsync')->__('Delete Listings');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($label)
            ->setOnClick("deleteListings()")
            ->toHtml();
            $html .= '<script type="text/javascript">function deleteListings(){
             if (confirm("Are you sure you want to delete all listings?")) {
                        new Ajax.Request(\''.$url.'\', {
                        method: \'get\',
                        onLoading: function (response) {
                        },
                        onComplete: function(response) {
                            var dataJson = response.responseText.evalJSON();
                            if(dataJson.success == true) {
                                alert(\'All listings successfully deleted\');
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