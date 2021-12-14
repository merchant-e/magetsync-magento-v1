<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for adding html script
 * Class Merchante_MagetSync_Block_Select_Render
 */
class Merchante_MagetSync_Block_Select_Render extends Varien_Data_Form_Element_Select
{
    /**
     * Method for adding html script to select field
     * @return string
     */
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        return $html." <small>".Mage::helper('magetsync')->__("This option lets you choose if this product will be synchronised with Etsy")."</small>";
    }

}