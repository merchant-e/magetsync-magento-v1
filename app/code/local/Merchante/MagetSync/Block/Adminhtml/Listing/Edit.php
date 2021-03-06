<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for enabled edit actions on Listing form
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Edit
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Edit extends
    Mage_Adminhtml_Block_Widget_Form_Container{

    /**
     * Initialize form container
     */
    public function __construct()
    {
        parent::__construct();
        /** @var  _objectId primary key*/
        $this->_objectId = 'id';
        /** @var  _blockGroup module name*/
        $this->_blockGroup = 'magetsync';
        /** @var  _controller controller name */
        $this->_controller = 'adminhtml_listing';
        $this->_updateButton('save', 'label',Mage::helper('magetsync')->__('Save listing'));
        $this->_updateButton('delete', 'label',Mage::helper('magetsync')->__('Delete listing'));
        $msgError = Mage::helper('magetsync')->__('Something went wrong');

        $dataRecord = Mage::registry('magetsync_data')->getData();
        $syncState = $dataRecord['sync'];
        $label = Mage::helper('magetsync')->__('ReSync');

        if (($syncState != Merchante_MagetSync_Model_Listing::STATE_SYNCED
             || $syncState != Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC
             || $syncState != Merchante_MagetSync_Model_Listing::STATE_FAILED)
            && !$dataRecord['listing_id'])
        {
            $label = Mage::helper('magetsync')->__('Sync Now');
        }

        // Single listing product edit
        if (Mage::registry('magetsync_massive') == null) {
            // Do not allow sync expired listing product
            if (!$this->isExpired($syncState)) {
                $this->_addButton('sync_now', array(
                    'label' => $label,
                    'onclick' =>
                        'if (editForm.validator && editForm.validator.validate()) {
                               $(\'edit_form\').request({method: \'post\',
                                 onSuccess: function(value){
                                    var myWindow = window.open(\'\', \'_self\');
                                    myWindow.document.write(value.responseText);
                                 },
                                 onFailure: function() { alert(\'' . $msgError . '\'); },
                                 parameters: { syncStatus:\'' . Merchante_MagetSync_Model_Listing::STATE_INQUEUE . '\' }});
                            } else {
                                 editForm.submit();
                            }',
                    'class' => 'save',
                ), 0, 100);
            } else {
                $label = Mage::helper('magetsync')->__('Renew');
                $this->_addButton('renew', array(
                    'label' => $label,
                    'onclick' =>
                        'new Ajax.Request(\'' . Mage::getModel('adminhtml/url')->getUrl('*/*/renew', array('listingId' => $dataRecord['listing_id'])) . '\',{
                             method: \'post\',
                             onSuccess: function(value){
                                var myWindow = window.open(\'\', \'_self\');
                                myWindow.document.write(value.responseText);
                             },
                             onFailure: function() { alert(\'' . $msgError . '\'); },
                             parameters: {\'magentoListingId\':'.$dataRecord['id'].', \'etsyListingId\':'.$dataRecord['listing_id'].'}});',
                    'class' => 'save',
                ), 0, 100);
            }

            $deleteFailedAllowed = Mage::getStoreConfig('magetsync_section_draftmode/magetsync_group_delete/magetsync_field_enable_failed_items_deletion');
            if (($deleteFailedAllowed && $syncState == Merchante_MagetSync_Model_Listing::STATE_FAILED)
                 || $this->isInQueue($syncState))
            {
                //Keep delete button
            } else {
                $this->_removeButton('delete');
            }
        // Mass attribute update
        } else {
            $this->_addButton('save_and_queue', array(
                'label'     => Mage::helper('magetsync')->__('Auto queue'),
                'onclick'   =>
                    'if (editForm.validator && editForm.validator.validate()) {
                       $(\'edit_form\').request({method: \'post\',
                         onSuccess: function(value){
                            var myWindow = window.open(\'\', \'_self\');
                            myWindow.document.write(value.responseText);
                         },
                         onFailure: function() { alert(\''.$msgError.'\'); },
                         parameters: { autoQueue:\'true\' }});
                    } else {
                         editForm.submit();
                    }',
                'class'     => 'add',
            ),0, 101);
        }
    }

    /**
     * Update header text in edit form
     * @return string
     */
    public function getHeaderText()
    {
        if( Mage::registry('magetsync_data')&&Mage::registry('magetsync_data')->getId())
        {
            return Mage::helper('magetsync')->__('Edit listing')." ".$this->htmlEscape(
                Mage::registry('magetsync_data')->getTitle()).'<br />';
        }
        else
        {
            if(Mage::registry('magetsync_massive')){
                $titleMassive = '';
                $massiveArray = explode(',',Mage::registry('magetsync_massive'));
                foreach($massiveArray as $item)
                {
                    $listing = Mage::getModel('magetsync/listing')->load($item);
                    $titleMassive = $listing->getTitle().', '.$titleMassive;
                }
                $newTitle = substr($titleMassive,0,strlen($titleMassive)-2);
                $newTitle = Mage::helper('magetsync')->__('Editing').' '.$newTitle;
                return Mage::helper('magetsync')->__($newTitle);
            }else
            {
                return Mage::helper('magetsync')->__('Add a listing');
            }
        }
    }

    /**
     * @param $syncState
     * @return bool
     */
    public function isExpired($syncState) {
        return $syncState == Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
    }

    /**
     * @param $syncState
     * @return bool
     */
    public function isInQueue($syncState) {
        return $syncState == Merchante_MagetSync_Model_Listing::STATE_INQUEUE || $syncState == Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE;
    }
}