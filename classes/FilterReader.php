<?php
/* --------------------------------------------------------------
  FilterReader.php 2020-07-24
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use Exception;

/**
 * Class GProtectorFilterReader
 */
class FilterReader
{

    /**
     * Reads the content of the Fallback FilterRule file and if it contains valid JSON, then return that content,
     * otherwise retry.
     *
     * @return mixed
     * @throws Exception
     */
    public function getFallbackFilterRules()
    {
        $localRules = $this->readFile(GAMBIO_PROTECTOR_LOCAL_FILERULES_DIR .
                                      GAMBIO_PROTECTOR_LOCAL_FILERULES_FILENAME);
        
        if ($this->isJsonValid($localRules)) {
            return json_decode($localRules, true);
        }
        
        return new Exception("Fallback standard.json file is invalid.");
    }
    
    
    /**
     * returns whether a given JSON string is valid JSON.
     *
     * @param string $jsonString
     *
     * @return bool
     */
    private function isJsonValid($jsonString)
    {
        json_decode($jsonString);
        return json_last_error() === JSON_ERROR_NONE;
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
     * Gets a List of Custom FilterRule files, passes these into getCustomFilesContent() and returns it.
     *
     * @return array
     */
    public function getCustomFilterRules()
    {
        $allFiles = scandir(GAMBIO_PROTECTOR_LOCAL_FILERULES_DIR);
        $filenames = array_diff($allFiles, ['..', '.', GAMBIO_PROTECTOR_LOCAL_FILERULES_FILENAME]);

        $filesContent = [];
        foreach ($filenames as $filename) {
            $rawFileContent = file_get_contents(GAMBIO_PROTECTOR_LOCAL_FILERULES_DIR . $filename);
            $jsonFileContent = json_decode($rawFileContent, true);
            $filesContent = array_merge($filesContent, $jsonFileContent);
        }
        return $filesContent;
    }
}
