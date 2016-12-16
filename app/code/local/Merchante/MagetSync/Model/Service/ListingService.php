<?php

/**
 * Class ListingService
 */
class Merchante_MagetSync_Model_Service_ListingService
{

    /**
     * @var Merchante_MagetSync_Model_Listing
     */
    protected $listingModel;

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
                         ->addFieldToFilter(
                             'sync', array('eq' => Merchante_MagetSync_Model_Listing::STATE_AUTO_QUEUE)
                         )
                         ->load();

        return $listings;
    }

    /**
     * Prepare data for request
     *
     * @param Merchante_MagetSync_Model_Listing $listing
     * @return array
     * @throws ListingServiceException
     */
    protected function prepareData(Merchante_MagetSync_Model_Listing $listing)
    {
        $data = $listing->getData();

        # Add new listing and sync the product
        /** @var Mage_Catalog_Model_Product $listingProduct */
        $listingProduct = Mage::getModel('catalog/product')->load($data['idproduct']);

        if (!$listingProduct->getId()) {
            throw new ListingServiceException(sprintf('Product with ID %s does not exists', $data['idproduct']));
        }

        $qty = round($listingProduct->getStockItem()->getQty(), 2);

        $supply = isset($data['is_supply']) ? $data['is_supply'] : 1;

        if ($supply == 1) {
            $supply = 0;
        } else {
            $supply = 1;
        }

        $language = Mage::helper('magetsync/config')->getMagetSyncLanguage();

        if (empty($language)) {
            throw new ListingServiceException(Mage::helper('magetsync')->__('Must configure Etsy\'s language'));
        }

        $stateListing = Merchante_MagetSync_Model_Listing::STATE_ACTIVE;

        if ($qty == 0) {
            $stateListing = Merchante_MagetSync_Model_Listing::STATE_INACTIVE;
            // To pass Etsy API restrictions
            $qty = 1;
        }

        $taxonomyID = $listing->getTaxonomyID($data);

        $params = array(
            'description'          => !empty($data['description']) ?: '',
            'materials'            => !empty($data['materials'])   ?: '',
            'state'                => $stateListing,
            'quantity'             => $qty,
            'price'                => $data['price'],
            'shipping_template_id' => !empty($data['shipping_template_id']) ?: '',
            'shop_section_id'      => !empty($data['shop_section_id']) ?: '',
            'title'                => !empty($data['title']) ?: '',
            'tags'                 => !empty($data['tags']) ?: '',
            'taxonomy_id'          => $taxonomyID,
            'who_made'             => !empty($data['who_made']) ?: '',
            'is_supply'            => $supply,
            'when_made'            => !empty($data['when_made']) ?: '',
            'recipient'            => !empty($data['recipient']) ?: '',
            'occasion'             => !empty($data['occasion']) ?: '',
            'style'                => !empty($data['style']) ?: '',
            'should_auto_renew'    => !empty($data['should_auto_renew']) ?: 0,
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
    public function processListingApi(Merchante_MagetSync_Model_Listing $listing)
    {
        $data = $listing->getData();

        # checking if the product is already there in the list synchronizing only images
        if ($data['listing_id']) {
            throw new Merchante_MagetSync_ListingServiceException(
                'Listing already exists',
                Merchante_MagetSync_ListingServiceException::ALREADY_EXISTS
            );
        }

        $params = $this->prepareData($listing);

        $hasError = false;

        $resultApi = $listing->createListing(null, $params);

        if ($resultApi['status'] == true) {

            $result = json_decode(json_decode($resultApi['result']), true);
            $result = $result['results'][0];
            $statusOperation =
                $listing->saveDetails($result, $data['idproduct'], $params['price'], $listing->getId());

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
        $listing->save();

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
     *
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
                $this->processListingApi($listing);
                $iterationCntr++;
            } catch (Exception $e) {

                if ($e instanceof OAuthException) {
                    $errorMsg = $e->lastResponse;
                } else {
                    $errorMsg = $e->getMessage();
                }

                Mage::log("Error: " . print_r($errorMsg, true), null, 'magetsync_listing.log');

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