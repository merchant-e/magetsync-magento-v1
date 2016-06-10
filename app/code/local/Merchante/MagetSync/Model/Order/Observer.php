<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Order_Observer
 */
class Merchante_MagetSync_Model_Order_Observer
{

    /**
     * @param $observer
     */
    public function orderState($observer)
    {
        try
        {
            $order = $observer->getOrder();
            if($order->getState() == Mage_Sales_Model_Order::STATE_NEW ||
                $order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING ||
                $order->getState() == Mage_Sales_Model_Order::STATE_CANCELED){
                $items = $order->getAllVisibleItems();
                foreach($items as $i) {
                    $listingModel = Mage::getModel('magetsync/listing');
                    $query = $listingModel->getCollection()->getSelect()->where('idproduct = '.$i->getProductId().'
                    AND quantity_has_changed ='.Merchante_MagetSync_Model_Listing::QUANTITY_HAS_NOT_CHANGED.' AND sync !='.
                    Merchante_MagetSync_Model_Listing::STATE_INQUEUE.' AND sync !='.
                    Merchante_MagetSync_Model_Listing::STATE_FAILED.' AND sync !='.
                    Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE.' AND sync !='.
                    Merchante_MagetSync_Model_Listing::STATE_EXPIRED);

                    $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
                    if($query) {
                        $dataSave['quantity_has_changed'] = Merchante_MagetSync_Model_Listing::QUANTITY_HAS_CHANGED;
                        $listingModel
                            ->addData($dataSave)
                            ->setId($query[0]['id']);
                        $listingModel->save();
                    }
                }
            }

        }catch (Exception $e)
        {
            //Mage::log("Error: ".print_r($e->getMessage(), true),null,'orderState.log');
        }
    }

    public function shipmentSaveAfter($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $orderCollection = Mage::getModel('magetsync/orderEtsy')->getCollection();
        $queryOrder = $orderCollection->getSelect()->where('order_id = ?', $order->getId());
        $queryOrder = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryOrder);
        if ($queryOrder) {
            $receiptModel = Mage::getModel('magetsync/receipt');

            $obligatoryParams = array('receipt_id'=>$queryOrder[0]['receipt_id']);
            $auxParams = array('was_shipped' => true);
            $resultReceipt = $receiptModel->updateReceipt($obligatoryParams,$auxParams);
            if($resultReceipt['status'] == true)
            {
                Mage::log("Info: " .$queryOrder[0]['receipt_id'] . ' - ' . $order->getId() , null, 'shipped.log');
            }else{
                Mage::log("Error: " . print_r($resultReceipt['message'], true), null, 'shipped.log');
            }

        }
    }

}