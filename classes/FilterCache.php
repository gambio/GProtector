<?php
/* --------------------------------------------------------------
  FilterCache.php 2020-07-24
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
     * Checks if the Cache File is older than the Remote FilterRules file
     *
     * @return bool
     */
    private function isCacheOlderThanRemoteFile()
    {
        $headers = get_headers(GAMBIO_PROTECTOR_REMOTE_FILTERRULES_URL, 1);
        if ($headers === false) {
            return false;
        }
        
        if (!strpos($headers[0], "200")) {
            return false;
        }
        
        $cacheFileAge  = filectime($this->cachedFilterRulesPath);
        $remoteFileAge = strtotime($headers["Last-Modified"]);
        
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
     * @throws Exception
     */
    private function readFile($path)
    {
        if (!file_exists($path)) {
            throw new Exception('Filter Rules file not found');
        }
        
        if (!is_readable($path)) {
            throw new Exception('Filter Rules file not readable');
        }
        
        return file_get_contents($path, 'r');
    }
    
    
    /**
     * Reads the content of the Cached FilterRule file and if it contains valid JSON, then return that content,
     * otherwise return the content of the Fallback FilterRules file.
     *
     * @return mixed
     * @throws Exception
     */
    public function getCachedFilterRules()
    {
        
        $cachedFilterRules = $this->readFile(GAMBIO_PROTECTOR_CACHE_DIR . GAMBIO_PROTECTOR_CACHE_FILERULES_FILENAME);
        
        if ($this->isJsonValid($cachedFilterRules)) {
            return $cachedFilterRules;
        } else {
            return $this->filterReader->getFallbackFilterRules();
        }
    }
    
    
    /**
     * Returns the remote FilterRule content via a HTTP GET Request.
     *
     * @return bool|string
     */
    private function getRemoteRules()
    {
        return file_get_contents(GAMBIO_PROTECTOR_REMOTE_FILTERRULES_URL);
    }
    
}