<?php

/**
 * Simple logger class to quickly log after initialization of log files setup.
 *
 * @copyright  Copyright (c) 2016 Merchant-e
 *  Class Merchante_MagetSync_Model_SimpleLogger
 */
class Merchante_MagetSync_Model_SimpleLogger implements Merchante_MagetSync_SimpleLoggerInterface
{
    /**
     * @var null|string
     */
    protected $logFile = null;

    /**
     * @var null|string
     */
    protected $exceptionLogFile = null;

    /**
     * @var string
     */
    protected $logMessagePrefix = '';

    /**
     * @var string
     */
    protected $bufferedMsg = '';

    /**
     * @var string
     */
    protected $prefixBeforeAppending = '';

    /**
     * @param string $msg
     * @param null|int $level - constants from Zend_Log class
     * @return $this
     */
    public function log($msg, $level = null)
    {
        $message = $this->logMessagePrefix . $msg;
        Mage::log($message, (null === $level) ? Zend_Log::INFO : $level, $this->logFile);

        return $this;
    }

    /**
     * @param Exception $e
     * @param null|string $message
     * @return $this
     */
    public function logException(Exception $e, $message = null)
    {
        try {
            $message = (null !== $message) ? $message : $e->getMessage();
            $this->log($message, Zend_Log::ERR);
            Mage::log($this->logMessagePrefix . $message . "\n" . $e->__toString(), Zend_Log::ERR, $this->exceptionLogFile);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFilePath - relative path to magento log dir
     * @return $this
     */
    public function setLogFile($logFilePath)
    {
        try {
            $logDir = Mage::getBaseDir('log');

            $fullFilePath = sprintf("%s/%s", $logDir, $logFilePath);
            $this->createPathIfNotExists($fullFilePath);

            $this->logFile = $logFilePath;
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getExceptionLogFile()
    {
        return $this->exceptionLogFile;
    }

    /**
     * @param string $logFilePath - relative path to magento log dir
     * @return $this
     */
    public function setExceptionLogFile($logFilePath)
    {
        try {
            $logDir = Mage::getBaseDir('log');

            $fullFilePath = sprintf("%s/%s", $logDir, $logFilePath);
            $this->createPathIfNotExists($fullFilePath);

            $this->exceptionLogFile = $logFilePath;
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * @param string $absoluteFilePath
     */
    public function createPathIfNotExists($absoluteFilePath)
    {
        $fileWriter = new Varien_Io_File();
        $fileWriter->checkAndCreateFolder(dirname($absoluteFilePath));
    }

    /**
     * @return string
     */
    public function getLogMessagePrefix()
    {
        return $this->logMessagePrefix;
    }

    /**
     * @param string $logMessagePrefix
     * @return $this
     */
    public function setLogMessagePrefix($logMessagePrefix)
    {
        $this->logMessagePrefix = $logMessagePrefix;

        return $this;
    }

    /**
     * @param string $appendedText
     * @return $this
     */
    public function appendLogMessagePrefix($appendedText)
    {
        $this->prefixBeforeAppending = $this->logMessagePrefix;
        $this->logMessagePrefix = $this->logMessagePrefix . " | " . $appendedText;

        return $this;
    }

    /**
     * @return $this
     */
    public function resetPrefixAppending()
    {
        $this->logMessagePrefix = $this->prefixBeforeAppending;

        return $this;
    }
}