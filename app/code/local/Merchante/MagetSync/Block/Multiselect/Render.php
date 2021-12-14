<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for adding html script
 * Class Merchante_MagetSync_Block_Multiselect_Render
 */
class Merchante_MagetSync_Block_Multiselect_Render extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Method for adding javascript
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setOnchange('convertAttribute(this);');
        $html = $element->getElementHtml();

        $html .= '<script type="text/javascript">
                    var regex = /\{\{(.*?)\}\}/g;
                    function convertAttribute(element) {
                       var appendArea = element.up("tr").next().next().down(".append-area");
                       var arr = element.getValue();
                       var appendAreaVal = appendArea.value;
                       var textToAdd = "";
                       var matchArr = appendAreaVal.match(regex);
                       if (matchArr) {
                           for (var i = 0; i < matchArr.length; i++) {
                                matchArr[i] = matchArr[i].replace("{{", "", "g").replace("}}", "", "g");
                                if (arr.indexOf(matchArr[i]) == -1) {
                                    appendAreaVal = appendAreaVal.replace("{{"+matchArr[i]+"}}", "", "g");
                                }
                            }
                       }
                       for (var i = 0; i < arr.length; i++) {
                           if (arr[i] && appendAreaVal.indexOf("{{"+arr[i]+"}}") == -1) {
                               textToAdd += "{{" + arr[i] + "}}";
                           }
                       }
                       appendArea.value = appendAreaVal + textToAdd;
                    }
                  </script>';

        return $html;
    }

}