<?php
/* --------------------------------------------------------------
  GProtectorFilter.php 2020-02-05
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

// @TODO: Remove after implementation. Just for Standalone/Testing purposes.
include_once __DIR__ . '/../config.inc.php';

/**
 * Class GProtectorFilterReader
 */
class GProtectorFilterReader
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function getDefaultFilterRules()
    {
        $remoteRules = $this->getRemoteRules();
        
        if ($remoteRules === false) {
            return $this->readFallbackFile();
        }
        
        if (!$this->isJsonValid($remoteRules)) {
            $this->readFallbackFile();
        }
            
        return json_decode($remoteRules, true);
        
    }
    
    
    /**
     * @return mixed
     * @throws Exception
     */
    private function readFallbackFile()
    {
        $localRules = $this->readFile(GAMBIO_PROTECTOR_LOCAL_FILERULES_DIR .
                                      GAMBIO_PROTECTOR_LOCAL_FILERULES_FILENAME);
        
        if ($this->isJsonValid($localRules)) {
            return json_decode($localRules, true);
        } else {
            $this->readFallbackFile();
        }
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
     * @return bool|string
     */
    private function getRemoteRules()
    {
        return file_get_contents(GAMBIO_PROTECTOR_REMOTE_FILTERRULES_URL);
    }
    
    
    /**
     *
     */
    public function getCustomFilterRules()
    {
        $customFileList = $this->getCustomFilterRulesFileList();
    }
    
    
    /**
     * @return array|false
     */
    private function getCustomFilterRulesFileList()
    {
        $allFiles = scandir(GAMBIO_PROTECTOR_LOCAL_FILERULES_DIR);
        array_shift($allFiles);
        array_shift($allFiles);
        return array_diff($allFiles, [GAMBIO_PROTECTOR_LOCAL_FILERULES_FILENAME]);
    }
}