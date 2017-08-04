<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Renderer_Pricing
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Renderer_Pricing
    extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    /**
     * @return string
     */
    public function getElementHtml()
    {
        if ($modelData = Mage::registry('magetsync_data')) {
            $dbRuleVal = $modelData['pricing_rule'];
            $dbAffectValue = $modelData['affect_value'];
            $dbAffectStrategy = $modelData['affect_strategy'];
        } else {
            $dbRuleVal = '';
            $dbAffectValue = '';
            $dbAffectStrategy = '';
        }

        $rulesArr = $this->getData('rules');
        $strategiesArr = $this->getData('strategies');

        $html = "<select id='pricing_rule' onchange='togglePricing(this);' name='pricing_rule' class='select' style='width:100px;margin-right:5px;'>";
        foreach($rulesArr as $rule) {
            $selected = $dbRuleVal == $rule['value'] ? "selected='selected'" : '';
            $html .= "<option value='" . $rule['value'] . "' " . $selected . ">" . $rule['label'] . "</option>";
        }
        $html .= "</select>";
        $html .= "<input id='affect_value' name='affect_value' value='" . $dbAffectValue . "' class='input-text' style='width:50px;margin-right:5px;display:none;' type='text'>";

        $html .= "<select id='affect_strategy' name='affect_strategy' class='select' style='width:60px;display:none;'>";
        foreach($strategiesArr as $strategy) {
            $selected = $dbAffectStrategy == $strategy['value'] ? "selected='selected'" : '';
            $html .= "<option value='" . $strategy['value'] . "' " . $selected . ">" . $strategy['label'] . "</option>";
        }
        $html .= "</select>";
        $html .= "<div>&nbsp;</div>";
        $html .= "<p>" . Mage::helper('magetsync')->__('Example: If original product price is $10, based on your selection, Etsy price will be $') . "<span id='estimate-price'>10</span></p>";
        $html .= $this->getAfterElementHtml();

        return $html;
     }

}