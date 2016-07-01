<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for creating button in system configuration
 * Class Merchante_MagetSync_Block_ButtonImportOrders
 */
class Merchante_MagetSync_Block_ButtonImportOrders extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Method for creating block with widget button
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/magetsync_index/importOrders');
        $label = Mage::helper('magetsync')->__('Import Orders Marked as Shipped');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($label)
            ->setOnClick("importOrders()")
            ->toHtml();
            $html .= '<script type="text/javascript">function importOrders(){
                        new Ajax.Request(\''.$url.'\', {
                        method: \'get\',
                        onLoading: function (response) {
                        },
                        onComplete: function(response) {
                            var dataJson = response.responseText.evalJSON();
                            if(dataJson.status == true) {
                                alert(\'Orders successfully imported\');
                            } else {
                                alert(dataJson.message);
                            }
                        }
                    });
                }
                </script>';

        return $html;
    }
}
?>