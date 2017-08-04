<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class to render listing form and add log section
 * Class Merchante_MagetSync_Block_Adminhtml_Global_Listing_Edit_Renderer_Listing
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_Listing
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('magetsync/logSection.phtml');
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