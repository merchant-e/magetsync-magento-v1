<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for rendering listing grid
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Grid_Renderer_Image
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
        if($val == Merchante_MagetSync_Model_Listing::STATE_INQUEUE)
        {
            $span =  "<span class='grid-severity-minor'><span>".Mage::helper('magetsync')->__('In queue')."</span></span>";
        }elseif($val == Merchante_MagetSync_Model_Listing::STATE_SYNCED)
        {
            $span =  "<span class='grid-severity-notice'><span>".Mage::helper('magetsync')->__(($row['state']=='draft'?'DRAFT/':'').'Synced')."</span></span>";
        } elseif($val == Merchante_MagetSync_Model_Listing::STATE_FAILED)
        {
            $span = "<span class='grid-severity-critical'><span>".Mage::helper('magetsync')->__('Failed')."</span></span>";
        }elseif($val == Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC)
        {
            $span = "<span class='grid-severity-major'><span>".Mage::helper('magetsync')->__(($row['state']=='draft'?'DRAFT/':'').'Out of sync')."</span></span>";
        }elseif($val == Merchante_MagetSync_Model_Listing::STATE_EXPIRED)
        {
            $span = "<span style='background-color:lightgray;text-transform:uppercase;font: bold 10px/16px Arial,Helvetica,sans-serif;padding:2px 20px;color:black;border-radius:9px;text-align:center'><span>".Mage::helper('magetsync')->__('Expired')."</span></span>";
        }elseif($val == Merchante_MagetSync_Model_Listing::STATE_MAPPED)
        {
            $span = "<span style='background-color:blue;text-transform:uppercase;font: bold 10px/16px Arial,Helvetica,sans-serif;padding:2px 20px;color:white;border-radius:9px;text-align:center'><span>".Mage::helper('magetsync')->__('Mapped')."</span></span>";

        }elseif($val == Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE)
        {
            $span = "<span style='background-color:#005b56;text-transform:uppercase;font: bold 10px/16px Arial,Helvetica,sans-serif;padding:2px 20px;color:white;border-radius:9px;text-align:center'><span>".Mage::helper('magetsync')->__('Queued')."</span></span>";

        }else{
            $url = $this->getUrl('adminhtml/magetsync_index/forceDelete');
            $label = Mage::helper('magetsync')->__('Force Delete');
            $urlIndex = $this->getUrl('adminhtml/magetsync_index/index');

            $span = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setId('btnForce'.$row->getData('id'))
                ->setType('button')
                ->setLabel($label)
                ->setOnClick("if (confirm('Are you sure you want to delete this listing?')) {
                        new Ajax.Request('".$url."', {
                                method: 'get',
                                parameters:{entity_id:'".$row->getData('id')."',listing_id:'"
                                .$row->getData('listing_id')."'},
                                onLoading: function (response) {
                                    document.getElementById('btnForce".$row->getData('id')."').disabled = true;
                                },
                                onSuccess: function(response) {
                                    var dataJson = response.responseText.evalJSON();
                                    if(dataJson.success == true) {
                                        //document.location.reload(true);
                                         window.open('".$urlIndex."','_self',false);
                                    } else {
                                        alert(dataJson.msg);
                                        document.getElementById('btnForce".$row->getData('id')."').disabled = false;
                                    }
                                },
                                onFailure:function(response)
                                {
                                    document.getElementById('btnForce".$row->getData('id')."').disabled = false;
                                }
                            });
             }else
            {
                document.location.reload(true);
            }")
                ->toHtml();
        }
        return $span;
    }


    public static function getStatesArray()
    {
        $options = array();
        $options['1'] = Mage::helper('magetsync')->__('In queue');
        $options['2'] = Mage::helper('magetsync')->__('Synced');
        $options['3'] = Mage::helper('magetsync')->__('Failed');
        $options['4'] = Mage::helper('magetsync')->__('Out of sync');
        $options['5'] = Mage::helper('magetsync')->__('Expired');
        $options['6'] = Mage::helper('magetsync')->__('Mapped');

        return $options;
    }

}