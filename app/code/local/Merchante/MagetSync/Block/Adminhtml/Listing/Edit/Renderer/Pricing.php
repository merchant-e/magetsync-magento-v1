<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_Pricing
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_Pricing
    extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $priceVal = '';
        $templateAssignment = 0;
        if ($modelData = Mage::registry('magetsync_data')) {
            $priceVal = $modelData->getPrice();
            $templateAssignment = $modelData->getAttributeTemplateId();
        }
        $html = "<input id='price' name='price' value='" . $priceVal . "' disabled='true' class='required-entry validate-not-negative-number input-text' type='text'>";
        $html .= "<input id='orig-price-val' name='orig-price-val' type='hidden' value='" . $priceVal . "'>";
        $html .= "<div><input id='custom-price' name='custom-price' type='checkbox'><span> " . Mage::helper('magetsync')->__('Enable Custom Pricing') . "</span></div>";
        if ($templateAssignment > 0) {
            $html .= "<div>&nbsp;</div>";
            $html .= "<p>" . Mage::helper('magetsync')->__('This price was applied globally. If edited, updates made to parent template won\'t affect this pricing.') . "</p>";
        }
        $html .= $this->getAfterElementHtml();

        return $html;
     }

}