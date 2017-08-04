<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Category
 */
class Merchante_MagetSync_Model_Category extends Merchante_MagetSync_Model_Etsy
{
    /**
     * @var string
     */
    public $name = 'Category';

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/category');
    }

    /**
     * @param null $parentTag
     * @return array
     */
    public function toOptionArray($parentTag = null)
    {
        $Collection = Mage::getModel('magetsync/category')->getCollection();
        if ($parentTag == null) {
            $query = $Collection->getSelect()->where('level = ?', 0);
        } else {

            $query = $Collection->getSelect()->where('parent_id= ?', $parentTag);
        }
        $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        $CArray = array(array('value' => null, 'label' => Mage::helper('magetsync')->__('Please Select')));

        $controlAux = 0;
        foreach ($query as $CList) {

            $CArray[] = array('value' => $CList['level_id'], 'label' => $CList['short_name']);
            $controlAux = 1;
        }

        if ($controlAux == 0) {
            return '';
        } else {
            return $CArray;
        }
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getCategory($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllTopCategory($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getSubCategory($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getSubSubCategory($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllTopCategoryChildren($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function findAllSubCategoryChildren($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    /**
     * @param $obligatory
     * @param null $params
     * @return mixed
     */
    public function getSellerTaxonomy($obligatory, $params = null)
    {
        return $this->selectExecute($this->name, __FUNCTION__, $obligatory, $params);
    }

    public function recursiveCategories($values, $installer)
    {
        foreach ($values as $value) {
            $newData = array();
            $newData['level'] = $value['level'];
            $newData['level_id'] = $value['id'];
            $newData['parent_id'] = $value['parent_id'];
            $newData['category_name'] = $value['path'];
            $newData['short_name'] = $value['name'];
            $newData['category_id'] = $value['category_id'];
            $installer->getConnection()->insert($installer->getTable('magetsync_category'), $newData);
            if ($value['children'] != null) {
                $this->recursiveCategories($value['children'], $installer);
            }

        }

    }

    /**
     * Method kept for backwards compatibility
     * @param $values
     * @param $connection
     * @param $tableName
     */
    public function recursiveCategoriesForTaxonomy($values, $connection, $tableName)
    {
        foreach ($values as $value) {
            $newData = array();
            $newData['level'] = $value['level'];
            $newData['level_id'] = $value['id'];
            $newData['parent_id'] = $value['parent_id'];
            $newData['category_name'] = $value['path'];
            $newData['short_name'] = $value['name'];
            $newData['category_id'] = $value['category_id'];
            $connection->insert($tableName, $newData);
            if ($value['children'] != null) {
                $this->recursiveCategoriesForTaxonomy($value['children'], $connection, $tableName);
            }

        }

    }

}