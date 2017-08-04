<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_MassPricing
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_MassPricing
    extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $rulesArr = $this->getData('rules');
        $strategiesArr = $this->getData('strategies');

        $html = "<select id='pricing_rule' onchange='togglePricing(this);' name='pricing_rule' class='select' style='width:100px;margin-right:5px;'>";
        foreach($rulesArr as $rule) {
            $html .= "<option value='" . $rule['value'] . "'>" . $rule['label'] . "</option>";
        }
        $html .= "</select>";
        $html .= "<input id='affect_value' name='affect_value' value='' class='input-text' style='width:50px;margin-right:5px;display:none;' type='text'>";

        $html .= "<select id='affect_strategy' name='affect_strategy' class='select' style='width:60px;display:none;'>";
        foreach($strategiesArr as $strategy) {
            $html .= "<option value='" . $strategy['value'] . "'>" . $strategy['label'] . "</option>";
        }
        $html .= "</select>";
        $html .= "<div>&nbsp;</div>";
        $html .= "<p>" . Mage::helper('magetsync')->__("Example: If original product price is $10, based on your selection, Etsy price will be $") . "<span id='estimate-price'>10</span></p>";
        $html .= $this->getAfterElementHtml();

        return $html;
     }

}