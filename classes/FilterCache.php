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
class FilterCache {

    /**
     * @var string
     */
    private $cachedFilterRulesPath;

    /**
     * @var
     */
    private $filterReader;

    /**
     * FilterCache constructor.
     */
    public function __construct()
    {
        $this->cachedFilterRulesPath = GAMBIO_PROTECTOR_CACHE_DIR . GAMBIO_PROTECTOR_CACHE_FILERULES_FILENAME;
        $this->filterReader = new FilterReader();
    }

    /**
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
     *
     */
    public function renewCacheIfNeeded() {
        // Check if file exists and is older than 8 hours
        if (file_exists($this->cachedFilterRulesPath) &&
            filemtime($this->cachedFilterRulesPath) + 8*60*60 < time()) {

            $this->createRemoteRulesCacheFile();
        } else {
            $this->createRemoteRulesCacheFile();
        }
    }


    /**
     *
     */
    private function createRemoteRulesCacheFile() {
        $remoteRules = $this->getRemoteRules();
        if ($this->isJsonValid($remoteRules)) {
            $this->deleteFile($this->cachedFilterRulesPath);
            $this->writeFile($this->cachedFilterRulesPath, $remoteRules);
        }
    }

    /**
     * @param $path
     */
    private function deleteFile($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * @param $filePath
     * @param $fileContent
     */
    private function writeFile($filePath, $fileContent) {
        file_put_contents($filePath, $fileContent);
    }

    /**
     * @param $path
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
     * @return mixed
     * @throws Exception
     */
    public function getCachedFilterRules()
    {

        $cachedFilterRules = $this->readFile(GAMBIO_PROTECTOR_CACHE_DIR .
            GAMBIO_PROTECTOR_CACHE_FILERULES_FILENAME);

        if ($this->isJsonValid($cachedFilterRules)) {
            return $cachedFilterRules;
        } else {
            return $this->filterReader->getFallbackFilterRules();
        }
    }

    /**
     * @return bool|string
     */
    private function getRemoteRules()
    {
        return file_get_contents(GAMBIO_PROTECTOR_REMOTE_FILTERRULES_URL);
    }

}