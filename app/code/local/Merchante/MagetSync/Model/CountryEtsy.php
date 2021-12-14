<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2021 Merchant-e
 * Class Merchante_MagetSync_Model_CountryEtsy
 */
class Merchante_MagetSync_Model_CountryEtsy extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'Country';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/countryEtsy');
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllCountry($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getCountry($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findByIsoCode($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $Collection = Mage::getModel('magetsync/countryEtsy')->getCollection();

        $CArray = array(
            array(
                'value' => null,
                'label' => Mage::helper('magetsync')->__('Please Select')
            )
        );

        foreach ($Collection as $CList) {

            $CArray[] = array(
                'value' => $CList['country_id'],
                'label' => $CList['name']
            );
        }

        return $CArray;
    }

    /**
     * @throws Exception
     */
    public function setAllCountries()
    {
        $countryModel = Mage::getModel('magetsync/countryEtsy');
        $resultApi = $this->findAllCountry(null, null);

        if ($resultApi['status'] == true) {
            $result = json_decode(json_decode($resultApi['result']), true);
            $result = (isset($result['results']) ? $result['results'] : null);
            foreach ($result as $value) {
                $keys = array_keys($value);
                $data = $countryModel->getCollection()->addFieldToFilter('country_id', $value['country_id']);
                $id = $data->getFirstItem();
                if (!$id->getId()) {
                    $total = count($value);
                    $entity = Mage::getModel('magetsync/countryEtsy');
                    for ($i = 0; $i <= $total - 1; $i++) {
                        $entity->setData($keys[$i], $value[$keys[$i]]);
                    }
                    $entity->save();
                }
            }
            $this->log("Error: " . print_r($resultApi, true));
        } else {
            $this->log("Error: " . print_r($resultApi, true));
        }
    }

}