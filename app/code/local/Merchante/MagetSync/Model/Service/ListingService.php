<?php

/**
 * Class ListingService
 */
class Merchante_MagetSync_Model_Service_ListingService extends Merchante_MagetSync_Model_Service_Abstract
{

    /**
     * @var Merchante_MagetSync_Model_Listing
     */
    protected $listingModel;

    /**
     * Constructor
     *
     * Merchante_MagetSync_Model_Service_ListingService constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setLogBaseName($this->getModel());
    }

    /**
     * Get listing model
     *
     * @return Merchante_MagetSync_Model_Listing
     */
    public function getModel()
    {
        if ($this->listingModel == null) {
            /** @var Merchante_MagetSync_Model_Listing $listingModel */
            $this->listingModel = Mage::getModel('magetsync/listing');
        }

        return $this->listingModel;
    }

    /**
     * Get listing collection
     *
     * @return Merchante_MagetSync_Model_Mysql4_Listing_Collection
     */
    public function getCollectionToProcess()
    {
        /** @var Merchante_MagetSync_Model_Mysql4_Listing_Collection $listings */
        $listings = $this->getModel()->getCollection()
                         ->addFieldToSelect('*')
                         ->addFieldToFilter('sync', ['in' => [
                                Merchante_MagetSync_Model_Listing::STATE_OUTOFSYNC,
                                Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE
                            ]
                        ])
                         ->load();

        return $listings;
    }

    /**
     * Prepare data for request
     *
     * @param Merchante_MagetSync_Model_Listing $listing
     * @return array
     * @throws Merchante_MagetSync_ListingServiceException
     */
    protected function prepareData(Merchante_MagetSync_Model_Listing $listing)
    {
        $data = $listing->getData();

        # Add new listing and sync the product
        /** @var Mage_Catalog_Model_Product $listingProduct */
        $listingProduct = Mage::getModel('catalog/product')->load($data['idproduct']);

        $qty = round($listingProduct->getStockItem()->getQty(), 2);

        $supply = isset($data['is_supply']) ? $data['is_supply'] : 1;

        if ($supply == 1) {
            $supply = 0;
        } else {
            $supply = 1;
        }

        $language = Mage::helper('magetsync/config')->getMagetSyncLanguage();

        if (empty($language)) {
            throw new Merchante_MagetSync_ListingServiceException(Mage::helper('magetsync')->__('Must configure Etsy\'s language'));
        }

        $stateListing = Merchante_MagetSync_Model_Listing::STATE_ACTIVE;

        if ($qty == 0) {
            $stateListing = Merchante_MagetSync_Model_Listing::STATE_INACTIVE;
            // To pass Etsy API restrictions
            $qty = 1;
        }

        $taxonomyID = $listing->getTaxonomyID($data);

        $newDescription = $listing->composeDescription(
            !empty($data['description']) ? $data['description'] : '',
            $data['prepended_template'],
            $data['appended_template'],
            $data['idproduct']
        );

        $params = array(
            'description'          => $data['description'] ?: '',
            'materials'            => $data['materials']   ?: '',
            'state'                => $stateListing,
            'quantity'             => $qty,
            'price'                => $data['price'],
            'shipping_template_id' => $data['shipping_template_id'] ?: '',
            'shop_section_id'      => $data['shop_section_id'] ?: '',
            'title'                => $data['title'] ?: '',
            'tags'                 => $data['tags'] ?: '',
            'taxonomy_id'          => $taxonomyID,
            'who_made'             => $data['who_made'] ?: '',
            'is_supply'            => $supply,
            'when_made'            => $data['when_made'] ?: '',
            'recipient'            => $data['recipient'] ?: '',
            'occasion'             => $data['occasion'] ?: '',
            'style'                => $data['style'] ?: '',
            'should_auto_renew'    => $data['should_auto_renew'] ?: 0,
            'language'             => $language
        );

        return $params;
    }

    /**
     * Process add listing API
     *
     * @param Merchante_MagetSync_Model_Listing $listing
     * @throws Exception
     * @throws ListingServiceException
     */
    public function processListingApi(Merchante_MagetSync_Model_Listing $listing, $update = false)
    {
        $data = $listing->getData();

        $params = $this->prepareData($listing);

        $price = $params['price'];
        $hasError = false;
        if (! $update) {
            $resultApi = $listing->createListing(null, $params);
        } else {
            $obliUpd = ['listing_id' => $listing->getListingId()];
            unset($params['price']);
            $resultApi = $listing->updateListing($obliUpd, $params);
        }

        if ($resultApi['status'] == true) {

            $result = json_decode(json_decode($resultApi['result']), true);
            $result = $result['results'][0];
            $statusOperation =
                $listing->saveDetails($result, $data['idproduct'], $price, $listing->getId());

            $postData['creation_tsz']           = $result['creation_tsz'];
            $postData['ending_tsz']             = $result['ending_tsz'];
            $postData['original_creation_tsz']  = $result['original_creation_tsz'];
            $postData['last_modified_tsz']      = $result['last_modified_tsz'];
            $postData['currency_code']          = $result['currency_code'];
            $postData['featured_rank']          = $result['featured_rank'];
            $postData['url']                    = $result['url'];
            $postData['views']                  = $result['views'];
            $postData['num_favorers']           = $result['num_favorers'];
            $postData['processing_min']         = $result['processing_min'];
            $postData['processing_max']         = $result['processing_max'];
            $postData['non_taxable']            = $result['non_taxable'];
            $postData['is_customizable']        = $result['is_customizable'];
            $postData['is_digital']             = $result['is_digital'];
            $postData['file_data']              = $result['file_data'];
            $postData['has_variations']         = $result['has_variations'];
            $postData['language']               = $result['language'];
            $postData['listing_id']             = $result['listing_id'];
            $postData['state']                  = $result['state'];
            $postData['user_id']                = $result['user_id'];

            if ($statusOperation['status']) {
                if ($result['state'] == 'edit') {
                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_EXPIRED;
                } else {
                    $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_SYNCED;
                }
            } else {
                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
                $hasError = true;
                if ($statusOperation['message']) {
                    $resultApi['message'] = $statusOperation['message'];
                } else {
                    $resultApi['message'] = Mage::helper('magetsync')->__('Error processing details');
                }
            }
        } else {
            $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FAILED;
            $hasError = true;
            if (strpos(
                    $resultApi['message'],
                    'The listing is not editable, must be active or expired but is removed'
                ) !== false
            ) {
                $postData['sync'] = Merchante_MagetSync_Model_Listing::STATE_FORCE_DELETE;
            }
        }
        $listing->addData($postData);

        try {
            $listing->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if ($hasError == true) {
            Merchante_MagetSync_Model_LogData::magetsync(
                $listing->getId(),
                Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                $resultApi['message'],
                Merchante_MagetSync_Model_LogData::LEVEL_ERROR
            );
        } else {
            // Clean logs
            $logData = Mage::getModel('magetsync/logData');
            $logData->remove($listing->getId(), Merchante_MagetSync_Model_LogData::TYPE_LISTING);

        }

    }

    /**
     * Process collection API
     */
    public function processListingCollectionApi()
    {
        $listings = $this->getCollectionToProcess();

        $iterationCntr = 0;

        /** @var Merchante_MagetSync_Model_Listing $listing */
        foreach ($listings as $listing) {

            if ($iterationCntr > Merchante_MagetSync_Model_Observer::AUTOQUEUE_ITERATIONS_LIMIT) {
                break;
            }
            try {
                if ($listing->getListingId()) {
                    $this->processListingApi($listing, true);
                    $listing->setSync(Merchante_MagetSync_Model_Listing::STATE_SYNCED);
                    $listing->setSyncready(1);
                    $listing->save();
                } else {
                    $this->processListingApi($listing);
                }
                $iterationCntr++;
            } catch (Exception $e) {

                if ($e instanceof OAuthException) {
                    $errorMsg = $e->lastResponse;
                } else {
                    $errorMsg = $e->getMessage();
                }

                $listing->log("Error: " . print_r($errorMsg, true));

                if ($listing->getId()) {
                    Merchante_MagetSync_Model_LogData::magetsync(
                        $listing->getId(),
                        Merchante_MagetSync_Model_LogData::TYPE_LISTING,
                        $errorMsg,
                        Merchante_MagetSync_Model_LogData::LEVEL_ERROR
                    );
                    /** @var Merchante_MagetSync_Model_Listing $listingModel */
                    $listingModel = Mage::getModel('magetsync/listing')->load($listing->getId());

                    $listingModel->addData(array('sync' => null))
                                 ->setId($listing->getId())
                                 ->save();
                }

            }
        }
    }

}