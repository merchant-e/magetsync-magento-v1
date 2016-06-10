<?php

class Merchante_MagetSync_Block_Adminhtml_Configuration_Template_Form
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'magetsync/systemHeader.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();

    }

}