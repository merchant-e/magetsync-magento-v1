<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class to render shipping template form and add shipping entries grid
 * Class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Edit_Renderer_ShippingTemplate
 */
class Merchante_MagetSync_Block_Adminhtml_Global_ShippingTemplate_Edit_Renderer_ShippingTemplate
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('magetsync/shippingentry.phtml');
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}