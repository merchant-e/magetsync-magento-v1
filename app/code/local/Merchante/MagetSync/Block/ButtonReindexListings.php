<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for creating reindex button in system configuration
 * Class Merchante_MagetSync_Block_ButtonReindexListings
 */
class Merchante_MagetSync_Block_ButtonReindexListings extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Method for creating block with widget button
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/magetsync_index/reindexListings');
        $label = Mage::helper('magetsync')->__('Re-Index');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($label)
            ->setOnClick("reindexListings()")
            ->toHtml();
            $html .= '<script type="text/javascript">function reindexListings(){
             if (confirm("Are you sure you want to reindex listings?")) {
                        new Ajax.Request(\''.$url.'\', {
                        method: \'get\',
                        onLoading: function (response) {
                        },
                        onComplete: function(response) {
                            var dataJson = response.responseText.evalJSON();
                            if(dataJson.success == true) {
                                alert(\'All listings with append/prepend templates auto-queued for re-syncing.\');
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