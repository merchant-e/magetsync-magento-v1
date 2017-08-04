<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class for rendering order grid
 * Class Merchante_MagetSync_Block_Adminhtml_Template_Grid_Renderer_Image
 */
class Merchante_MagetSync_Block_Adminhtml_Template_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Override render method
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    /**
     * Method for getting row's value and set status image
     * @param Varien_Object $row
     * @return string
     */
    protected function _getValue(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        $alt = '';
        if($val!='')
        {
            $url = $url.'adminhtml/default/default/Merchante/images/etsy-icon.png';
            $alt = Mage::helper('magetsync')->__('Order coming from Etsy');
        }
        if($val=='')
        {
            $url = $url.'adminhtml/default/default/Merchante/images/magento-icon.png';
            $alt = Mage::helper('magetsync')->__('Order coming from Magento');
        }
        $out = "<img alt='".$alt."' src=". $url ." width='24px'/>";
        return $out;
    }

}