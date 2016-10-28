<?php

/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class templates grid initialization
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit extends
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
        $this->_controller = 'adminhtml_attributeTemplate';
        $this->_updateButton('save', 'label', Mage::helper('magetsync')->__('Save template'));
        $this->_updateButton('save', 'sort_order', 100);
        $this->_updateButton('save', 'level', 0);
        $this->_updateButton('delete', 'label', Mage::helper('magetsync')->__('Delete template'));
        $msgError = Mage::helper('magetsync')->__('Something went wrong');

        $this->_addButton('sync_now', array(
            'label'     => Mage::helper('magetsync')->__('Save and Sync All'),
            'onclick'   =>
                'if (editForm.validator && editForm.validator.validate()) {
                   $(\'edit_form\').request({method: \'post\',
                     onSuccess: function(value){
                        var myWindow = window.open(\'\', \'_self\');
                        myWindow.document.write(value.responseText);
                     },
                     onFailure: function() { alert(\''.$msgError.'\'); },
                     parameters: { queueListings:\'true\' }});
                } else {
                     editForm.submit();
                }',
            'class'     => 'save',
        ),0, 101);

        if ($templateId = $this->getRequest()->getParam('id')) {
            $this->_addButton('duplicate', array(
                'label' => Mage::helper('magetsync')->__('Duplicate'),
                'onclick' => "setLocation('" . $this->getUrl('*/*/duplicate', array('templateToDuplicateId' => $templateId)) . "')",
                'class' => 'go'
            ), 0, 99);
        }
    }

    /**
     * Update header text in edit form
     * @return string
     */
    public function getHeaderText()
    {
        if( Mage::registry('magetsync_data') && Mage::registry('magetsync_data')->getId())
        {
            return Mage::helper('magetsync')->__('Edit attribute template')." ".$this->htmlEscape(
                Mage::registry('magetsync_data')->getTitle()).'<br />';
        }
        else
        {
            return Mage::helper('magetsync')->__('Edit attribute template');
        }
    }
}