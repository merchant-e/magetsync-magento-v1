<?php
/**
 * @copyright  Copyright (c) 2016 Merchant-e
 *
 * Class Merchante_MagetSync_Model_Service_Abstract
 */
abstract class Merchante_MagetSync_Model_Service_Abstract
{
    /**
     * Base log name for API service classes
     *
     * @var
     */
    protected $logBaseName;


    public function __construct()
    {
        $this->setLogBaseName(Merchante_MagetSync_Helper_Log::DEFAULT_LOG_BASE_NAME);
    }

    /**
     * Set base log name
     *
     * @param $object
     */
    protected function setLogBaseName($object)
    {
        if (is_object($object)) {
            $this->logBaseName = strtolower(get_class($object));
        }

        if (is_string($object)) {
            $this->logBaseName = $object;
        }

    }

    /**
     * Get Log base name
     *
     * @return string
     */
    protected function getLogBaseName()
    {
        return $this->logBaseName;
    }
}