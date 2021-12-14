<?php

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 *
 * Class for creating template form
 * Class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tab_Form
 */
class Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldsetGlobal = $form->addFieldset('magetsync_form_global_section',
            array('legend'=>Mage::helper('magetsync')->__("Global Section")));

        $fieldsetGlobal->addField('prepended_template', 'select', array(
            'name'  => 'prepended_template',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Prepend Global Note"),
            'values'    => array(
                null => 'Select template',
                1   => Mage::helper('magetsync')->__('Note').' 1',
                2   => Mage::helper('magetsync')->__('Note').' 2'
            ),
        ));
        $fieldsetGlobal->addField('appended_template', 'select', array(
            'name'  => 'appended_template',
            'required' => false,
            'label'     => Mage::helper('magetsync')->__("Append Global Note"),
            'values'    => array(
                null => 'Select template',
                1   => Mage::helper('magetsync')->__('Note').' 1',
                2   => Mage::helper('magetsync')->__('Note').' 2'
            ),
        ));

        $fieldsetGlobal->addField('should_auto_renew', 'select', array(
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

        $fieldsetAbout->addField('is_supply', 'select', array(
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

        $fieldsetAbout->addField('when_made', 'select', array(
            'name'  => 'when_made',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("When was it made?"),
            'disabled' => true,
            'values'    => Mage::getModel('magetsync/whenMade')->toOptionArray(),
        ));

        $fieldsetAbout->addType('pricingrule', 'Merchante_MagetSync_Block_Adminhtml_AttributeTemplate_Edit_Renderer_Pricing');
        $pricingRule = $fieldsetAbout->addField('pricing_rule', 'pricingrule', array(
            'name'  => 'pricing_rule',
            'class'  => 'validate-select',
            'required' => true,
            'label'     => Mage::helper('magetsync')->__("Pricing Rule"),
            'disabled' => false,
            'rules'    => Mage::getModel('magetsync/attributeTemplate')->toPricingRuleOptionArray(),
            'strategies'    => Mage::getModel('magetsync/attributeTemplate')->toPricingStrategyOptionArray(),
            'style'     => 'width:100px;',
        ));

        $dataMagetsy = Mage::registry('magetsync_data');
        $filtersub = '';
        $filtersubsub = '';
        $filtersub4 = '';
        $filtersub5 = '';
        $filtersub6 = '';
        $filtersub7 = '';

        if ($dataMagetsy) {
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

        $fieldsetSearch->addField('materials', 'text',
            array(
                'label' => Mage::helper('magetsync')->__("Materials"),
                'name' => 'materials',
            ));

        $fieldsetSS = $form->addFieldset('magetsync_form_ss',
            array('legend'=> Mage::helper('magetsync')->__("Shipping and Shop information")));

        $fieldsetSS->addField('shop_section_id', 'select', array(
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

        $valuesAttributeTemplate =  Mage::registry('magetsync_data')->getData();
        $subCategoryAux    = '';
        $subSubCategoryAux = '';
        $subCategoryAux4   = '';
        $subCategoryAux5   = '';
        $subCategoryAux6   = '';
        $subCategoryAux7   = '';

        if($valuesAttributeTemplate['subcategory_id'] == null){ $subCategoryAux = '$(\'subcategory_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subsubcategory_id'] == null){ $subSubCategoryAux = '$(\'subsubcategory_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory4_id'] == null){ $subCategoryAux4 = '$(\'subcategory4_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory5_id'] == null){ $subCategoryAux5 = '$(\'subcategory5_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory6_id'] == null){ $subCategoryAux6 = '$(\'subcategory6_id\').up(0).up(0).hide();';}
        if($valuesAttributeTemplate['subcategory7_id'] == null){ $subCategoryAux7 = '$(\'subcategory7_id\').up(0).up(0).hide();';}

        $shippingTemplate->setAfterElementHtml("<script type=\"text/javascript\">
                    ".$subCategoryAux."
                    ".$subSubCategoryAux."
                    ".$subCategoryAux4."
                    ".$subCategoryAux5."
                    ".$subCategoryAux6."
                    ".$subCategoryAux7."
                </script>");

        if ( Mage::registry('magetsync_data') )
        {
            $form->setValues(Mage::registry('magetsync_data')->getData());
            $form->getElement('is_supply')->setDisabled(false);
            $form->getElement('when_made')->setDisabled(false);
        }

        //Moved to bottom to prevent data loss during setting values
        $fieldsetSS->addField('category_reload_url', 'hidden', array(
            'name'  => 'category_reload_url',
            'value' => $this->getUrl('adminhtml/magetsync_index/category')
        ));
        $fieldsetSS->addField('category_template_id', 'hidden', array(
            'name'  => 'category_listing_id',
            'value' => $valuesAttributeTemplate['id']
        ));


        return parent::_prepareForm();
    }
}