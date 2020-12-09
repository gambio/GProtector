<?php
/* --------------------------------------------------------------
  FilterCache.php 2020-12-09
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;


/**
 * Class FilterCache
 */
class FilterCache
{
    
    /**
     * @var string $cachedFilterRulesPath
     */
    private $cachedFilterRulesPath;
    
    /**
     * @var FilterReader $filterReader
     */
    private $filterReader;
    
    /**
     * FilterCache constructor.
     */
    public function __construct()
    {
        $this->cachedFilterRulesPath = GAMBIO_PROTECTOR_CACHE_DIR . GAMBIO_PROTECTOR_CACHE_FILERULES_FILENAME;
        $this->filterReader          = new FilterReader();
    }
    
    
    /**
     * Reads the content of the Cached FilterRule file and if it contains valid JSON, then return that content,
     * otherwise return the content of the Fallback FilterRules file.
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCachedFilterRules()
    {
        
        $cachedFilterRules = $this->readFile(GAMBIO_PROTECTOR_CACHE_DIR . GAMBIO_PROTECTOR_CACHE_FILERULES_FILENAME);
        
        if ($this->isJsonValid($cachedFilterRules)) {
            return json_decode($cachedFilterRules, true);
        } else {
            return $this->filterReader->getFallbackFilterRules();
        }
    }
    
    
    /**
     * If the Cached FilterRule file does not exists or is older than 8 hours, Download and recreate the file.
     *
     */
    public function renew()
    {
        if (!file_exists($this->cachedFilterRulesPath)) {
            $this->createRemoteRulesCacheFile();
            
            return;
        }
        
        if (filemtime($this->cachedFilterRulesPath) + GAMBIO_PROTECTOR_CACHE_RENEW_INTERVAL < time()
            && $this->isCacheOlderThanRemoteFile()) {
            $this->createRemoteRulesCacheFile();
        } else {
            touch($this->cachedFilterRulesPath, time());
        }
    }
    
    
    /**
     * Checks Whether a string is a Valid JSON string.
     *
     * @param $jsonString
     *
     * @return bool
     */
    private function isJsonValid($jsonString)
    {
        json_decode($jsonString);
        
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    
    /**
     * Checks if the Cache File is older than the Remote FilterRules file
     *
     * @return bool
     */
    private function isCacheOlderThanRemoteFile()
    {
        $connection = curl_init();
        $timeout    = 5;

        curl_setopt($connection, CURLOPT_URL, GAMBIO_PROTECTOR_REMOTE_FILTERRULES_URL);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_HEADER, true);
        curl_setopt($connection, CURLOPT_FILETIME, true);
        curl_setopt($connection, CURLOPT_NOBODY, true);
        curl_setopt($connection, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($connection, CURLOPT_MAXREDIRS, 10);

        curl_exec($connection); // needed for curl_errno check
        $headers = curl_getinfo($connection);

        if (curl_errno($connection) || !isset($headers['http_code']) || $headers['http_code'] !== 200) {
            return false;
        }

        curl_close($connection);

        $cacheFileAge  = filectime($this->cachedFilterRulesPath);
        $remoteFileAge = $headers['filetime'];

        if ($remoteFileAge > $cacheFileAge) {
            return true;
        }

        return false;
    }
    
    
    /**
     * Checks if the remote FilterRules are Valid JSON and if it is, delete the old cache file and create a new
     * one with the received FilterRules.
     *
     */
    private function createRemoteRulesCacheFile()
    {
        $remoteRules = $this->getRemoteRules();
        if ($this->isJsonValid($remoteRules)) {
            $this->deleteFile($this->cachedFilterRulesPath);
            $this->writeFile($this->cachedFilterRulesPath, $remoteRules);
        }
    }
    
    
    /**
     * If a given file does exists, delete it.
     *
     * @param string $filePath
     */
    private function deleteFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    
    /**
     * Writes a file with a given Path and Content.
     *
     * @param string $filePath
     * @param string $fileContent
     */
    private function writeFile($filePath, $fileContent)
    {
        file_put_contents($filePath, $fileContent);
    }
    
    
    /**
     * If a given file exists and is readable, return the file content.
     *
     * @param string $path
     *
     * @return false|string
     * @throws InvalidArgumentException
     */
    private function readFile($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('Filter Rules file not found');
        }
        
        if (!is_readable($path)) {
            throw new InvalidArgumentException('Filter Rules file not readable');
        }
        
        return file_get_contents($path, 'r');
    }
    
    
    /**
     * Returns the remote FilterRule content via a HTTP GET Request.
     *
     * @return bool|string
     */
    private function getRemoteRules()
    {
        $connection = curl_init();
        $timeout = 5;
        
        curl_setopt($connection, CURLOPT_URL, GAMBIO_PROTECTOR_REMOTE_FILTERRULES_URL);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($connection, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($connection, CURLOPT_MAXFILESIZE, 2000000);
        curl_setopt($connection, CURLOPT_MAXREDIRS, 10);
        
        $content = curl_exec($connection);
        
        curl_close($connection);
        
        return $content;
    }
    
}