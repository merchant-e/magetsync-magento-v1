<?php

/**
 * @copyright  Copyright (c) 2017 Merchant-e
 *
 * Class Merchante_MagetSync_Helper_File
 */
class Merchante_MagetSync_Helper_File extends Mage_Core_Helper_Abstract
{

    /**
     * Method for loading file to array
     *
     * @param string $pathFile
     * @return array
     */
    public function getVerificationFile($pathFile)
    {
        $responseFile = [];

        if (!file_exists($pathFile)) {
            return [];
        }

        $file = fopen($pathFile, "r");

        while(!feof($file)) {
            $responseFile[] = trim(fgets($file));
        }

        fclose($file);

        return $responseFile;
    }

    /**
     * Method for reading file and setting line in the last part
     *
     * @param $pathFile
     * @param $data
     * @param bool $end
     */
    public function setDataLine($pathFile, $data, $end = false)
    {
        $fp = fopen($pathFile, "a");

        $line = '';

        if(!$end) {
            $line = PHP_EOL;
        }

 	    fwrite($fp, $data . $line);
     	fclose($fp);
    }
}