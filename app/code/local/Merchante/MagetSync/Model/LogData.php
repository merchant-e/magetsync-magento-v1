<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Model_LogData
 */
class Merchante_MagetSync_Model_LogData extends Merchante_MagetSync_Model_Etsy
{

    const LEVEL_SUCCESS       = 1;
    const LEVEL_ERROR         = 2;
    const LEVEL_WARNING       = 3;
    const LEVEL_NOTICE        = 4;

    const TYPE_LISTING        = 1;

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/logData');
    }

    public function remove($entity_id,$entity_type)
    {
         $collection = Mage::getModel('magetsync/logData')->getCollection()
            ->getSelect()->where('entity_id ='. $entity_id .' AND entity_type ='.$entity_type);

        Mage::getSingleton('core/resource_iterator')->walk($collection, array(array($this, 'deleteLogCallback')));

    }
    public function deleteLogCallback($args)
    {
        $log = Mage::getModel('magetsync/logData');
        $log->setData($args['row']);
        $log->delete();
    }

    public static function magetsync($entity_id,$entity_type,$message,$level)
    {
        $date = new DateTime();
        $newDate =$date->format('Y-m-d H:i:s');
        $logModel = Mage::getModel('magetsync/logData');
        $query = $logModel->getCollection()->getSelect()->where('entity_id ='. $entity_id
            .' AND entity_type ='.$entity_type. ' AND message =\''. addslashes($message).'\'');
        $results = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        $logInfo = array();
        $logInfo['date'] = $newDate;
        if(!$results) {
            $logInfo['entity_id'] = $entity_id;
            $logInfo['entity_type'] = $entity_type;
            $logInfo['message'] = $message;
            $logInfo['level_error'] = $level;
            $logModel->addData($logInfo)->save();
        }else{
            $logModel
                ->addData($logInfo)
                ->setId($results[0]['id']);
            $logModel->save();
        }

    }

}