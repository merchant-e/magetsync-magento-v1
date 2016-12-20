<?php
/**
 * @copyright  Copyright (c) 2015 Merchant-e
 *
 * Class Merchante_MagetSync_Helper_Log
 */
class Merchante_MagetSync_Helper_Log extends Mage_Core_Helper_Abstract
{
    /**
     * @var Merchante_MagetSync_Model_SimpleLogger
     */
    protected $logger = [];

    /**
     * Name of default exception log name (log IG)
     */
    const DEFAULT_LOG_BASE_NAME               = 'etsy';

    /**
     * Get (Create) logger object
     *
     * @return Merchante_MagetSync_SimpleLoggerInterface
     * @throws Merchante_MagetSync_ApiClientException
     */
    protected function getLogger($loggerBaseName, $prefix = 'Magetsync')
    {
        if (!isset($this->logger[$loggerBaseName]) || $this->logger[$loggerBaseName] === null) {
            /** @var Merchante_MagetSync_Model_SimpleLogger logger[$loggerBaseName] */
            $this->logger[$loggerBaseName] = Mage::getModel('magetsync/simpleLogger');

            if (false === $this->logger[$loggerBaseName]) {
                throw new Merchante_MagetSync_ApiClientException("No logger class found");
            }

            $exceptionFile = 'magetsync' . DS . $loggerBaseName . '.log';
            $logFile       = 'magetsync' . DS . $loggerBaseName. '.log';

            $this->logger[$loggerBaseName]->setExceptionLogFile($exceptionFile);
            $this->logger[$loggerBaseName]->setLogFile($logFile);
            $this->logger[$loggerBaseName]->setLogMessagePrefix(sprintf('[%s]', $prefix));
        }

        return $this->logger[$loggerBaseName];
    }

    /**
     * Log into file
     *
     * @param $message
     * @throws Merchante_MagetSync_ApiClientException
     */
    public function log($message, $loggerBaseName = null)
    {
        if (!Mage::helper('magetsync/config')->isLogEnabled()) {

            return;
        }

        if ($loggerBaseName === null) {
            $loggerBaseName = self::DEFAULT_LOG_BASE_NAME;
        }

        $this->getLogger($loggerBaseName)->log($message);
    }

    /**
     * Log exception
     *
     * @param $message
     * @throws Merchante_MagetSync_ApiClientException
     */
    public function logException($message, $loggerBaseName = null)
    {
        if (!Mage::helper('magetsync/config')->isLogExceptionEnabled()) {

            return;
        }

        if ($loggerBaseName === null) {
            $loggerBaseName = sprintf('%s_exception', self::DEFAULT_LOG_BASE_NAME);
        }

        $this->getLogger($loggerBaseName)->logException($message);
    }

}