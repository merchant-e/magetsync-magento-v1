<?php
/**
 * Simple logger interface.
 * @copyright  Copyright (c) 2016 Merchant-e
 * Class Merchante_MagetSync_SimpleLoggerInterface
 */
interface Merchante_MagetSync_SimpleLoggerInterface
{
    /**
     * @param string $msg
     * @param null|int $level - constants from Zend_Log class
     * @return $this
     */
    public function log($msg, $level = null);

    /**
     * @param Exception $e
     * @param null|string $message
     * @return $this
     */
    public function logException(Exception $e, $message = null);

    /**
     * @return string
     */
    public function getLogMessagePrefix();

    /**
     * @param string $logMessagePrefix
     * @return $this
     */
    public function setLogMessagePrefix($logMessagePrefix);
}