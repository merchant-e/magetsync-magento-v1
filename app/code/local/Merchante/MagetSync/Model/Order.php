<?php

/**
 * @copyright  Copyright (c) 2015 Merchant-e
 * Class Merchante_MagetSync_Model_Order
 */
class Merchante_MagetSync_Model_Order extends Merchante_MagetSync_Model_Etsy
{
    const PAYMENT_CCSAVE = 'ccsave';
    const PAYMENT_PAYPAL_STANDARD = 'paypal_standard';
    const PAYMENT_CHECKMO = 'checkmo';
    const PAYMENT_AUTHORIZENET = 'authorizenet';
    const PAYMENT_PAYPAL_EXPRESS = 'paypal_express';
    const PAYMENT_MAGETSYNC = 'magetsync_payment';

    public function _construct()
    {
        parent::_construct();
        $this->_init('magetsync/order');
    }

    public function setOptionArray($values, $arrOptions, $optionID, $option_type_id = 'option_type_id')
    {
        if ($values) {
            $first = reset($values);
            $arrOptions[$optionID] = $first[$option_type_id];
        } else {
            $arrOptions[$optionID] = '0';
        }

        return $arrOptions;
    }

    public function makeOrder($was_shipped = 0)
    {
        /********************/
        $etsyModel = Mage::getSingleton('magetsync/etsy');
        $tokenCustomer = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_tokencustomer');

        $url = Merchante_MagetSync_Model_Etsy::$merchApi;

        if (! $tokenCustomer) {

            return [
                'status'  => false,
                'message' => 'Customer token empty.'
            ];
        }

            $url = $url . "customerVerification/" . $tokenCustomer;
            $response = $etsyModel->curlConnect($url);
            $response = json_decode($response, true);
            $dataApi['message'] = '';
            /*******************/
            if ($response['success'] && $response['authorized']) {
                $receipt = Mage::getModel('magetsync/receipt');
                $shop = Mage::getStoreConfig('magetsync_section/magetsync_group/magetsync_field_shop');
                $obligatory = array('shop_id' => $shop);
                $limit = 25;
                $offset = 0;
                $totalGlobal = 0;
                do {
                    $totalReceipts = 0;
                    $params = array(
                        'includes'    => 'Listings,Transactions,Country,Buyer/Profile',
                        'limit'       => $limit,
                        'offset'      => $offset,
                        'was_shipped' => $was_shipped
                    );
                    $dataApi = $receipt->findAllShopReceipts($obligatory, $params);
                    if ($dataApi['status'] == true) {
                        $results = json_decode(json_decode($dataApi['result']), true);
                        $results = $results['results'];
                        $totalReceipts = count($results);
                        $totalGlobal = $totalGlobal + $totalReceipts;
                        $allActivePaymentMethods = Mage::getModel('payment/config')->getActiveMethods();
                        $currentCurrency = Mage::app()->getStore()->getDefaultCurrencyCode();
                        foreach ($results as $value) {
                            $orderCollection = Mage::getModel('magetsync/orderEtsy')->getCollection();
                            $queryOrder = $orderCollection->getSelect()->where('receipt_id = ?', $value['receipt_id']);
                            $queryOrder =
                                Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($queryOrder);
                            if (!$queryOrder) {
                                #TODO: Move to top
                                $storeSelected = Mage::getStoreConfig(
                                    'magetsync_section/magetsync_group_options/magetsync_field_magento_store'
                                );

                                if ($storeSelected) {
                                    $quote = Mage::getModel('sales/quote')
                                                 ->setStoreId($storeSelected);
                                } else {
                                    $quote = Mage::getModel('sales/quote')
                                                 ->setStoreId(Mage::app()->getStore('default')->getId());
                                }
                                $customer = Mage::getModel('customer/customer')
                                                ->setWebsiteId(1)
                                                ->loadByEmail($value['buyer_email']);
                                $idCustomer = $customer->getId();
                                if ($idCustomer) {
                                    // for customer orders:
                                    $quote->assignCustomer($customer);
                                } else {
                                    // for guest orders only:
                                    $quote->setCustomerEmail($value['buyer_email']);
                                }
                                $transactionNumber = '';
                                if ($value['payment_method'] == 'pp') {

                                    $subject = $value['message_from_payment'];
                                    $pattern = '/txn_id=(\w*)/';
                                    preg_match($pattern, $subject, $matches);
                                    $transactionNumber = $matches[1];

                                }
                                $qtyProducts = array();
                                $k = 0;
                                foreach ($value['Transactions'] as $list) {
                                    $listingCollection = Mage::getModel('magetsync/listing')->getCollection();
                                    $query =
                                        $listingCollection->getSelect()->where('listing_id = ?', $list['listing_id']);
                                    $query = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll(
                                        $query
                                    );
                                    if ($query) {
                                        //add product(s)
                                        $product = Mage::getModel('catalog/product')->load($query[0]['idproduct']);

                                        $dataPro = $product->getData();

                                        if ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                                            $options = $product->getOptions();
                                        } elseif ($dataPro['type_id'] ==
                                            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                        ) {
                                            $options = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                                        }

                                        $listVariations = $list['variations'];
                                        $arrOptions = array();
                                        if ($listVariations) {
                                            foreach ($options as $opt) {
                                                $find = false;
                                                if ($dataPro['type_id'] ==
                                                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                                                ) {
                                                    $values = $opt->getValues();
                                                    $optionID = 'option_id';
                                                    $option_type_id = 'option_type_id';
                                                    $title = 'title';
                                                } elseif ($dataPro['type_id'] ==
                                                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                                ) {
                                                    $values = $opt['values'];
                                                    $optionID = 'attribute_id';
                                                    $option_type_id = 'value_index';
                                                    $title = 'label';
                                                }
                                                foreach ($listVariations as $val) {
                                                    if (strtolower($val['formatted_name']) ===
                                                        strtolower($opt[$title])
                                                    ) {
                                                        if ($values) {
                                                            foreach ($values as $valAux) {
                                                                if (strtolower($valAux[$title]) ==
                                                                    strtolower($val['formatted_value'])
                                                                ) {
                                                                    $arrOptions[$opt[$optionID]] =
                                                                        $valAux[$option_type_id];
                                                                    $find = true;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        if ($find) {
                                                            break;
                                                        }
                                                    }
                                                }


                                                if ($dataPro['type_id'] ==
                                                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                                                ) {
                                                    $isRequired = $opt['is_require'];
                                                } elseif ($dataPro['type_id'] ==
                                                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                                ) {
                                                    $requiredObj =
                                                        $product->getResource()->getAttribute($opt['attribute_code']);
                                                    $isRequired = $requiredObj['is_required'];
                                                }

                                                if ($isRequired) {
                                                    if (!$find) {
                                                        $arrOptions = $this->setOptionArray(
                                                            $values, $arrOptions, $opt[$optionID], $option_type_id
                                                        );
                                                    }
                                                }
                                            }
                                        } else {
                                            foreach ($options as $opt) {

                                                if ($dataPro['type_id'] ==
                                                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                                                ) {
                                                    $values = $opt->getValues();
                                                    $optionID = 'option_id';
                                                    $option_type_id = 'option_type_id';
                                                    $isRequired = $opt['is_require'];
                                                } elseif ($dataPro['type_id'] ==
                                                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                                ) {
                                                    $values = $opt['values'];
                                                    $optionID = 'attribute_id';
                                                    $option_type_id = 'value_index';
                                                    $requiredObj =
                                                        $product->getResource()->getAttribute($opt['attribute_code']);
                                                    $isRequired = $requiredObj['is_required'];
                                                }

                                                if ($isRequired) {
                                                    $arrOptions = $this->setOptionArray(
                                                        $values, $arrOptions, $opt[$optionID], $option_type_id
                                                    );
                                                }
                                            }
                                        }

                                        $buyInfo = array();
                                        $buyInfo['qty'] = $list['quantity'];
                                        if (count($arrOptions) > 0) {
                                            if ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                                                $buyInfo['options'] = $arrOptions;
                                            } elseif ($dataPro['type_id'] ==
                                                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                            ) {
                                                $buyInfo['super_attribute'] = $arrOptions;
                                            }
                                        }
                                        $qty = 0;
                                        if ($dataPro['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                                            $qty = $query[0]['quantity'];
                                        } elseif ($dataPro['type_id'] ==
                                            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                        ) {
                                            foreach ($product->getTypeInstance(true)->getUsedProducts(
                                                null, $product
                                            ) as $simple) {
                                                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct(
                                                    $simple
                                                )->getQty();
                                                $stock = round($stock, 2);
                                                $qty += $stock;
                                            }
                                        }

                                        $qtyProducts[$k]['id'] = $query[0]['id'];
                                        $qtyProducts[$k]['qty'] = intval($qty) - intval($list['quantity']);

                                        $objBuyInfo = null;
                                        if (count($buyInfo) > 0) {
                                            $objBuyInfo = new Varien_Object($buyInfo);
                                        }

                                        $newCurrency = $list['currency_code'];
                                        $priceEtsy = $list['price'];

                                        if ($newCurrency && $priceEtsy && $currentCurrency) {
                                            if ($newCurrency != $currentCurrency) {
                                                $newPrice = Mage::helper('magetsync/data')->convertValue(
                                                    $newCurrency, floatval($priceEtsy)
                                                );
                                                if (!$newPrice) {
                                                    $newPrice = $priceEtsy;
                                                }
                                            } else {
                                                $newPrice = $priceEtsy;
                                            }
                                            if ($newPrice) {
                                                $quote->addProduct($product, $objBuyInfo)->setOriginalCustomPrice(
                                                    $newPrice
                                                );
                                            } else {
                                                $quote->addProduct($product, $objBuyInfo);
                                            }
                                        } else {
                                            $quote->addProduct($product, $objBuyInfo);
                                        }
                                        $k = $k + 1;
                                    }
                                }
                                $countryCollection = Mage::getModel('magetsync/countryEtsy')->getCollection();
                                $query = $countryCollection->getSelect()->where(
                                    'country_id = ?', $value['country_id']
                                );//$list['listing_id']);
                                $query =
                                    Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);

                                $country_code = 0;
                                if ($query && count($query) > 0) {
                                    $country_code = $query[0]['iso_country_code'];
                                } else {
                                    if ($value['Country'] && $value['Country']['iso_country_code']) {
                                        $country_code = $value['Country']['iso_country_code'];
                                    }
                                }

                                $region_id = 0;
                                $region =
                                    Mage::getModel('directory/region')->loadByCode($value['state'], $country_code);
                                if ($region != null && $region->getData('region_id') != null) {
                                    $region_id = $region->getData('region_id');
                                } else {
                                    if ($value['state']) {
                                        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                                        $directory_region = Mage::getSingleton('core/resource')->getTableName(
                                            'directory_country_region'
                                        );
                                        if ($directory_region) {
                                            $sql = 'INSERT INTO ' . $directory_region .
                                                ' (region_id,country_id,code,default_name) VALUES (NULL,?,?,?)';
                                            $connection->query(
                                                $sql, array(
                                                $country_code,
                                                $value['state']
                                                ,
                                                $value['state']
                                            )
                                            );
                                            $region_id = $connection->lastInsertId();
                                            if (!$region_id) {
                                                $region_id = 0;
                                            }
                                        } else {
                                            $region_id = 0;
                                        }
                                    } else {
                                        $region_id = 0;
                                    }
                                }

                                if ($value['name']) {
                                    $value_name = trim($value['name']);
                                    $dataName = explode(' ', $value_name, 2);
                                    if (count($dataName) > 0) {
                                        $firstName = $dataName[0];
                                        if (count($dataName) == 1) {
                                            $lastName = $dataName[0];
                                        } else {
                                            $lastName = $dataName[1];
                                        }
                                    } else {
                                        $firstName = '-';
                                        $lastName = '-';
                                    }
                                } else {
                                    $firstName = $value['Buyer']['Profile']['first_name'];
                                    $lastName = $value['Buyer']['Profile']['last_name'];
                                    if (!$firstName) {
                                        $firstName = '-';
                                    }
                                    if (!$lastName) {
                                        $lastName = '-';
                                    }
                                }


                                if (!$value['zip']) {
                                    $zip = '-;';
                                } else {
                                    $zip = $value['zip'];
                                }

                                $addressData = array(
                                    'firstname'  => $firstName,
                                    'lastname'   => $lastName,
                                    'street'     => $value['first_line'] . ' ' . $value['second_line'],
                                    'city'       => $value['city'],
                                    'postcode'   => $zip,
                                    'telephone'  => '0',
                                    'country_id' => $country_code,
                                    'region_id'  => $region_id
                                );

                                $paymentMethod = Merchante_MagetSync_Model_Order::PAYMENT_MAGETSYNC;
                                $useMagetsyncPMOnly = Mage::getStoreConfig('magetsync_section/magetsync_group_sales_order/magetsync_field_default_payment_method');
                                if ($value['payment_method'] == 'pp' && !$useMagetsyncPMOnly) {
                                    if (isset($allActivePaymentMethods[Merchante_MagetSync_Model_Order::PAYMENT_PAYPAL_STANDARD])) {
                                        $paymentMethod = Merchante_MagetSync_Model_Order::PAYMENT_PAYPAL_STANDARD;
                                    } elseif (isset($allActivePaymentMethods[Merchante_MagetSync_Model_Order::PAYMENT_PAYPAL_EXPRESS])) {
                                        $paymentMethod = Merchante_MagetSync_Model_Order::PAYMENT_PAYPAL_EXPRESS;
                                    }
                                }



                                $this->setOrder(
                                    $quote, $addressData, $value, $qtyProducts, $paymentMethod, $transactionNumber,
                                    $was_shipped
                                );
                            }
                        }

                        $offset = $offset + $limit;

                    } else {
                        $messageOrder = '';
                        if ($totalGlobal > 0) {
                            $messageOrder = "imported Orders: " . $totalGlobal . "\n";
                        }

                        return array(
                            'status'  => false,
                            'message' => $messageOrder . $dataApi['message']
                        );
                    }
                } while ($totalReceipts > 0);

                return array('status' => true);
            } else {
                return array(
                    'status'  => false,
                    'message' => 'Customer is not authorized.'
                );
                /****NOT AUTHORIZED YET****/
                //Mage::log("Error: ".print_r($dataApi['message'], true),null,'magetsync_order.log');
            }

    }

    /**
     * Set Order
     *
     * @param $quote
     * @param $addressData
     * @param $value
     * @param null $qtyProducts
     * @param null $paymentMethod
     * @param null $transactionNumber
     * @param int $was_shipped
     * @throws Exception
     * @throws bool
     */
    public function setOrder($quote, $addressData, $value, $qtyProducts = null, $paymentMethod = null,
        $transactionNumber = null, $was_shipped = 0
    ) {
        $quote->getBillingAddress()->addData($addressData);
        $shippingAddress = $quote->getShippingAddress()->addData($addressData);


        Mage::helper('magetsync/data')->unsetValue('shipping_magetsync_data');
        $shippingInfo = array('shipping_price' => $value['total_shipping_cost']);
        Mage::helper('magetsync/data')->setValue('shipping_magetsync_data', $shippingInfo);

        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                        ->setShippingMethod('magetsync_shipping_magetsync_shipping')
                        ->setPaymentMethod($paymentMethod);

        $quote->getPayment()->importData(array('method' => $paymentMethod));

        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);

        $is_changed = false;
        $coreConfig = Mage::getModel('core/config');
        $region_search = $addressData['country_id'];
        if ($addressData['region_id'] == 0) {
            $region_values = Mage::getStoreConfig('general/region/state_required');
            $regionArray = explode(',', $region_values);
            $region_exist = array_search($region_search, $regionArray);

            if ($region_exist !== false) {
                $is_changed = true;
                $regionRequiredCountries = explode(',', Mage::getStoreConfig('general/region/state_required'));
                $regionRequiredCountries = array_diff($regionRequiredCountries, array($region_search));
                $regionRequiredCountries = implode(',', $regionRequiredCountries);
                $coreConfig->saveConfig('general/region/state_required', $regionRequiredCountries);
                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();
            }
        }

        $service->submitAll();

        if ($is_changed) {
            $regionRequiredCountries = explode(',', Mage::getStoreConfig('general/region/state_required'));
            $regionRequiredCountries[] = $region_search;
            $regionRequiredCountries = implode(',', $regionRequiredCountries);
            $coreConfig->saveConfig('general/region/state_required', $regionRequiredCountries);
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $service->getOrder();
        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $usePrefix =
                Mage::getStoreConfig('magetsync_section/magetsync_group_sales_order/magetsync_field_enable_prefix');
            if ($usePrefix) {
                $prefix =
                    Mage::getStoreConfig('magetsync_section/magetsync_group_sales_order/magetsync_field_order_prefix');

                $incrementId = $order->getRealOrderId();

                if ($incrementId) {
                    $order->setIncrementId($prefix . $incrementId);
                }
            }

            if ($transactionNumber) {
                /***** Transaction ID ******/
                $payment = $order->getPayment();

                $payment->setTransactionId($transactionNumber)
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(0)
                        ->registerCaptureNotification(0, true);

            }

            if ($value['message_from_buyer']) {
                $msgFromBuyer = $value['message_from_buyer'];
            } else {
                $msgFromBuyer = Mage::helper('magetsync')->__('There\'s no note from buyer.');
            }

            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
            $order->addStatusHistoryComment($msgFromBuyer, Mage_Sales_Model_Order::STATE_PROCESSING);
            if ($was_shipped == 1) {
                $current_time = Varien_Date::formatDate($value['creation_tsz'], false);
                $order->setCreatedAt($current_time);
                $order->setUpdatedAt($current_time);
            }

            try {
                $order->save();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('magetsync')->__('Error saving order #'. $order->getId()));
                $this->logException($e->getMessage());
            }

            /****UPDATE QUANTITIES IN LISTINGS****/

            foreach ($qtyProducts as $qtyItem) {
                $listingUpdate = Mage::getModel('magetsync/listing');
                $postData['quantity'] = $qtyItem['qty'];
                $listingUpdate
                    ->addData($postData)
                    ->setId($qtyItem['id'])
                    ->save();
            }
            /************************************/

            $orderEtsyData = array(
                'is_order_etsy' => 1,
                'order_id'      => $order->getId(),
                'receipt_id'    => $value['receipt_id']
            );
            $orderECollection = Mage::getModel('magetsync/orderEtsy');
            $orderECollection->addData($orderEtsyData)->setId(null);;
            $orderECollection->save();


            /***CREATE INVOICE***/

            if (!$order->canInvoice()) {
                Mage::log("Error: Cannot create an invoice.", null, 'magetsync_invoice.log');
            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::log("Error: Cannot create an invoice without products.", null, 'magetsync_invoice.log');
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                                   ->addObject($invoice)
                                   ->addObject($invoice->getOrder());

            $transactionSave->save();

            /********************/


        } else {
            $this->log("Error: " . print_r($value['receipt_id'], true));
        }
    }


}