<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class for creating Listing form
 * Class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Tab_Form
 */
class Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Method for creating form inputs
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldsetGlobal = $form->addFieldset('magetsync_form_global_section',
            array('legend'=>Mage::helper('magetsync')->__("Global Section")));

        $prependedTemplate = $fieldsetGlobal->addField('prepended_template', 'select', array(
            'name'  => 'prepended_template',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Prepend Global Note"),
            'values'    => array(
                null => 'Select template',
                1   => Mage::helper('magetsync')->__('Note').' 1',
                2   => Mage::helper('magetsync')->__('Note').' 2'
            ),
        ));

        $appendedTemplate = $fieldsetGlobal->addField('appended_template', 'select', array(
            'name'  => 'appended_template',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Append Global Note"),
            'values'    => array(
                null => 'Select template',
                1   => Mage::helper('magetsync')->__('Note').' 1',
                2   => Mage::helper('magetsync')->__('Note').' 2'
            ),
        ));

        $renewalOption = $fieldsetGlobal->addField('should_auto_renew', 'select', array(
            'name'  => 'should_auto_renew',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Automatically renew listing"),
            'values'    => array(
                null => 'Select renewal option',
                1   => Mage::helper('magetsync')->__('Yes'),
                0   => Mage::helper('magetsync')->__('No')
            ),
        ));

        $fieldsetAbout = $form->addFieldset('magetsync_form_about',
            array('legend'=>Mage::helper('magetsync')->__("About information")));

        $whoMade = $fieldsetAbout->addField('who_made', 'select', array(
            'name'  => 'who_made',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("Who Made it?"),
            'values'    => Mage::getModel('magetsync/whoMade')->toOptionArray(),
            'onchange' => 'getNextData(this)',
        ));

        $whatIs = $fieldsetAbout->addField('is_supply', 'select', array(
            'name'  => 'is_supply',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("What is it?"),
            'disabled' => true,
            'values'    => array(
                null => 'Select a use',
                1   => Mage::helper('magetsync')->__('A finished product'),
                2   => Mage::helper('magetsync')->__('A supply or tool to make things')
            ),
        ));

        $whereMade = $fieldsetAbout->addField('when_made', 'select', array(
            'name'  => 'when_made',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("When was it made?"),
            'disabled' => true,
            'values'    => Mage::getModel('magetsync/whenMade')->toOptionArray(),
        ));

        //Single listing edit
        if(!Mage::registry('magetsync_massive')) {
            $fieldsetAbout->addType('pricingrule', 'Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_Pricing');
            $pricingRule = $fieldsetAbout->addField('pricing_rule', 'pricingrule', array(
                'name'  => 'price',
                'required' => true,
                'label'     => Mage::helper('magetsync')->__("Price")
            ));

        //Mass attribute update
        } else {
            $fieldsetAbout->addType('pricingrule', 'Merchante_MagetSync_Block_Adminhtml_Listing_Edit_Renderer_MassPricing');
            $pricingRule = $fieldsetAbout->addField('pricing_rule', 'pricingrule', array(
                'name'  => 'pricing_rule',
                'label'     => Mage::helper('magetsync')->__("Pricing Rule"),
                'rules'    => Mage::getModel('magetsync/attributeTemplate')->toPricingRuleOptionArray(),
                'strategies'    => Mage::getModel('magetsync/attributeTemplate')->toPricingStrategyOptionArray(),
            ));
        }


        $dataMagetsy = Mage::registry('magetsync_data');
        $filtersub = '';
        $filtersubsub = '';
        $filtersub4 = '';
        $filtersub5 = '';
        $filtersub6 = '';
        $filtersub7 = '';

        if ( $dataMagetsy ) {
            if ($dataMagetsy['category_id'] <> '') {
                $filtersub = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['category_id']);
            }
            if ($dataMagetsy['subcategory_id'] <> '') {
                $filtersubsub = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory_id']);
            }
            if ($dataMagetsy['subsubcategory_id'] <> '') {
                $filtersub4 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subsubcategory_id']);
            }
            if ($dataMagetsy['subcategory4_id'] <> '') {
                $filtersub5 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory4_id']);
            }
            if ($dataMagetsy['subcategory5_id'] <> '') {
                $filtersub6 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory5_id']);
            }
            if ($dataMagetsy['subcategory6_id'] <> '') {
                $filtersub7 = Mage::getModel('magetsync/category')->toOptionArray($dataMagetsy['subcategory6_id']);
            }
        }

        $fieldsetCategories = $form->addFieldset('magetsync_form_category',
            array('legend' => Mage::helper('magetsync')->__("Category information")));

        $propertiesHolder = $form->addFieldset('properties_holder',
            array('legend' => Mage::helper('magetsync')->__("Attributes")));

        $placeholderText = Mage::helper('magetsync')->__("Make it easier for buyers to find this listing by adding more details.");
        $placeholderLinkText = Mage::helper('magetsync')->__("Learn more about attributes.");
        $placeholderLink = "<a target='_blank' href='https://www.etsy.com/help/article/95284237119'>$placeholderLinkText</a>";
        $propertiesHolder->addField('placeholder', 'note', array(
            'text' => "<div class='properties-holder'>" . $placeholderText . ' ' . $placeholderLink . "</div>"
        ));

        $category = $fieldsetCategories->addField('category_id', 'select', array(
            'name'  => 'category_id',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("Category"),
            'values'    => Mage::getModel('magetsync/category')->toOptionArray(),
            'onchange' => 'getCategory(this)',
        ));

        $subCategory = $fieldsetCategories->addField('subcategory_id', 'select', array(
            'name'  => 'subcategory_id',
            'label'     => Mage::helper('magetsync')->__("Category 2"),
            'required' => false,
            'values' => $filtersub,
            'onchange' => 'getSubCategory(this)'
        ));

        $subSubCategory = $fieldsetCategories->addField('subsubcategory_id', 'select', array(
            'name'  => 'subsubcategory_id',
            'label'     => Mage::helper('magetsync')->__("Category 3"),
            'required' => false,
            'values' =>  $filtersubsub,
            'onchange' => 'getSubSubCategory(this)'
        ));

        $subCategory4 = $fieldsetCategories->addField('subcategory4_id', 'select', array(
            'name'  => 'subcategory4_id',
            'label'     => Mage::helper('magetsync')->__("Category 4"),
            'required' => false,
            'values' =>  $filtersub4,
            'onchange' => 'getSubCategory4(this)'
        ));

        $subCategory5 = $fieldsetCategories->addField('subcategory5_id', 'select', array(
            'name'  => 'subcategory5_id',
            'label'     => Mage::helper('magetsync')->__("Category 5"),
            'required' => false,
            'values' =>  $filtersub5,
            'onchange' => 'getSubCategory5(this)'
        ));

        $subCategory6 = $fieldsetCategories->addField('subcategory6_id', 'select', array(
            'name'  => 'subcategory6_id',
            'label'     => Mage::helper('magetsync')->__("Category 6"),
            'required' => false,
            'values' =>  $filtersub6,
            'onchange' => 'getSubCategory6(this)'
        ));

        $subCategory7 = $fieldsetCategories->addField('subcategory7_id', 'select', array(
            'name'  => 'subcategory7_id',
            'label'     => Mage::helper('magetsync')->__("Category 7"),
            'required' => false,
            'values' =>  $filtersub7
        ));

        $fieldsetSearch = $form->addFieldset('magetsync_form_search',
            array('legend'=> Mage::helper('magetsync')->__("Search information")));

        $materials = $fieldsetSearch->addField('materials', 'text',
            array(
                'label' => Mage::helper('magetsync')->__("Materials"),
                'name' => 'materials',
            ));

        $fieldsetSS = $form->addFieldset('magetsync_form_ss',
            array('legend'=> Mage::helper('magetsync')->__("Shipping and Shop information")));

        $shopSection = $fieldsetSS->addField('shop_section_id', 'select', array(
            'name'  => 'shop_section_id',
            'label'     => Mage::helper('magetsync')->__("Shop section"),
            'class'  => 'validate-select',
            'required' => true,
            'values'    => Mage::getModel('magetsync/shopSection')->toOptionArray(),
        ));

        $shippingTemplate = $fieldsetSS->addField('shipping_template_id', 'select', array(
            'name'  => 'shipping_template_id',
            'label'     => Mage::helper('magetsync')->__("Shipping profile"),
            'class'  => 'validate-select',
            'required' => true,
            'values'    => Mage::getModel('magetsync/shippingTemplate')->toOptionArray(),
        ));

        $subCategoryAux    = '';
        $subSubCategoryAux = '';
        $subCategoryAux4   = '';
        $subCategoryAux5   = '';
        $subCategoryAux6   = '';
        $subCategoryAux7   = '';

        $valuesListing =  Mage::registry('magetsync_data')->getData();
        if($valuesListing['subcategory_id'] == null){ $subCategoryAux = '$(\'subcategory_id\').up(0).up(0).hide();';}
        if($valuesListing['subsubcategory_id'] == null){ $subSubCategoryAux = '$(\'subsubcategory_id\').up(0).up(0).hide();';}
        if($valuesListing['subcategory4_id'] == null){ $subCategoryAux4 = '$(\'subcategory4_id\').up(0).up(0).hide();';}
        if($valuesListing['subcategory5_id'] == null){ $subCategoryAux5 = '$(\'subcategory5_id\').up(0).up(0).hide();';}
        if($valuesListing['subcategory6_id'] == null){ $subCategoryAux6 = '$(\'subcategory6_id\').up(0).up(0).hide();';}
        if($valuesListing['subcategory7_id'] == null){ $subCategoryAux7 = '$(\'subcategory7_id\').up(0).up(0).hide();';}

        $shippingTemplate->setAfterElementHtml("<script type=\"text/javascript\">
                    ".$subCategoryAux."
                    ".$subSubCategoryAux."
                    ".$subCategoryAux4."
                    ".$subCategoryAux5."
                    ".$subCategoryAux6."
                    ".$subCategoryAux7."
                </script>");

        /**
         * Field for transport multiple listings selects
         */
        $ids = $fieldsetSS->addField('listingids', 'hidden', array(
            'name'  => 'listingids',
        ));

        $fieldsetLog = $form->addFieldset('magetsync_form_log',
            array('legend'=> Mage::helper('magetsync')->__("Log Section")));

        /*********************************
         * Log Section (.phtml)
         ********************************/
        $entry_field = $fieldsetLog->addField('log_section', 'editor', array(
            'name'      => 'log_section',
            'label'     => Mage::helper('magetsync')->__("Log Section")
        ));
        $log_section = $form->getElement('log_section');
        $log_section->setRenderer(
            $this->getLayout()->createBlock('magetsync/adminhtml_listing_edit_renderer_listing')
        );
        /***********************************************************************/


        if ( Mage::registry('magetsync_data') )
        {
            $form->setValues(Mage::registry('magetsync_data')->getData());
            $form->getElement('is_supply')->setDisabled(false);
            $form->getElement('when_made')->setDisabled(false);
        }
            $form->addValues(array('listingids' => Mage::registry('magetsync_massive')));

        //Moved to bottom to prevent data loss during setting values
        $fieldsetSS->addField('category_reload_url', 'hidden', array(
            'name'  => 'category_reload_url',
            'value' => $this->getUrl('adminhtml/magetsync_index/category')
        ));
        $fieldsetSS->addField('category_listing_id', 'hidden', array(
            'name'  => 'category_listing_id',
            'value' => $valuesListing['id']
        ));

        return parent::_prepareForm();
    }
}