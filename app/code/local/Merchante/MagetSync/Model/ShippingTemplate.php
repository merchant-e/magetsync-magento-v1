<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_ShippingTemplate
 */
class Merchante_MagetSync_Model_ShippingTemplate extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'ShippingTemplate';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/shippingTemplate');
    }

    /**
     * @param $shippingId
     */
    public function setEntries($shippingId)
    {
        $shippingModel = Mage::getModel('magetsync/shippingEntry')->getCollection()->
            getSelect()->where('shipping_template_id= ?',$shippingId);
        $shippingResult = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($shippingModel);
        if(count($shippingResult)> 0)
        {
            $resultArr = array();
            $i = 0;
            foreach($shippingResult as $result)
            {
                $OriCountryModel = Mage::getModel('magetsync/countryEtsy')->getCollection();
                $queryOrigin = $OriCountryModel->getSelect()->where('country_id= ?',$result['origin_country_id']);
                $countryOriginResult = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryOrigin);
                $result['countryOrigin'] = $countryOriginResult[0];

                $DestCountryModel = Mage::getModel('magetsync/countryEtsy')->getCollection();
                $queryDestination = $DestCountryModel->getSelect()->where('country_id = ?',$result['destination_country_id']);
                $countryDestinationResult = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryDestination);
                $result['countryDestination'] = $countryDestinationResult[0];
                $resultArr[$i] = $result;
                $i++;
            }
        }
        $this->_data['entries'] = $resultArr;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/shippingTemplate')->getCollection();
        $CArray = array(array('value'=>null, 'label'=>Mage::helper('magetsync')->__('Please Select')));
        foreach ($Collection as $CList){

            $CArray[] = array('value'=>$CList['shipping_template_id'],'label'=>$CList['title']);
        }
        return $CArray;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function createShippingTemplate($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getShippingTemplate($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function updateShippingTemplate($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function deleteShippingTemplate($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllShippingTemplateEntries($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllUserShippingProfiles($obligatory, $params = null)
    {
        $result = $this->selectExecute($this->name,__FUNCTION__,$obligatory,$params);
        return $result;
    }

}