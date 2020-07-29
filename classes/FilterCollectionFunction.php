<?php
/* --------------------------------------------------------------
  FilterCollectionFunction.php 2020-07-28
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

class FilterCollectionFunction
{
    private $filterCollection;
    
    /**
     * @return FilterCollection
     * @throws Exception
     */
    private function readFilterFiles()
    {
        if (file_exists(GAMBIO_PROTECTOR_CACHE_DIR . GAMBIO_PROTECTOR_CACHE_FILERULES_FILENAME)) {
            $rawFilters = $this->filterCache()->getCachedFilterRules() + $this->filterReader()->getCustomFilterRules();
        } else {
            $rawFilters = $this->filterReader()->getFallbackFilterRules() + $this->filterReader()->getCustomFilterRules();
        }
        
        $filterArray = [];
        foreach ($rawFilters as $rawFilter) {
            $key = new Key($rawFilter['key']);
            
            $scriptNames = [];
            if (is_array($rawFilter['script_name'])) {
                foreach ($rawFilter['script_name'] as $scriptName) {
                    $scriptNames[] = new ScriptName($scriptName);
                }
            } else {
                $scriptNames[] = new ScriptName($rawFilter['script_name']);
            }
            $scriptNameCollection = new ScriptNameCollection($scriptNames);
            
            $variables = [];
            foreach ($rawFilter['variables'] as $variableName) {
                $variables[] = new Variable($variableName['type'], $variableName['property']);
            }
            $variableCollection = new VariableCollection($variables);
            $method             = new Method($rawFilter['function']);
            $severity           = new Severity($rawFilter['severity']);
            $filter             = new Filter(
                $key, $scriptNameCollection, $variableCollection, $method, $severity
            );
            $filterArray[]      = $filter;
        }
        
        return new FilterCollection($filterArray);
    }
    
    
    /**
     * @param FilterCollection $filters
     */
    private function applyFilters(FilterCollection $filters)
    {
        $this->filterCollection = $filters;
        $this->addFilters($filters);
        $this->filter();
    }
    
    
    /**
     * @param FilterCollection $filters
     */
    private function addFilters(FilterCollection $filters)
    {
        $this->filterCollection = $filters;
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }
    
    private function filter()
    {
        if (is_array($this->filterArray)) {
            foreach ($this->filterArray as $filterName => $dataArray) {
                if (isset($valueReference)) {
                    unset($valueReference);
                }
                
                if (is_array($dataArray) && isset($dataArray['script_name_array'])
                    && is_array(
                        $dataArray['script_name_array']
                    )) {
                    foreach ($dataArray['script_name_array'] as $scriptPath) {
                        if ($this->isScript($scriptPath) === true) {
                            if (isset($dataArray['function'])) {
                                $function       = (string)$dataArray['function'];
                                $functionPrefix = $this->getFunctionPrefix();
                                $function       = $functionPrefix . $function;
                                
                                if (function_exists($function)) {
                                    if (isset($dataArray['variables_array'])
                                        && is_array(
                                            $dataArray['variables_array']
                                        )) {
                                        foreach ($dataArray['variables_array'] as $variable) {
                                            $variableString = (string)$variable;
                                            
                                            $arrayBracketPos    = (int)strpos($variableString, '[');
                                            $variableNameEndPos = strlen($variableString);
                                            
                                            if ($arrayBracketPos > 0) {
                                                $variableNameEndPos = $arrayBracketPos;
                                            }
                                            
                                            if ($variableNameEndPos > 0) {
                                                $variableName = substr($variableString, 0, $variableNameEndPos);
                                                
                                                global ${$variableName};
                                                
                                                $variableReference =& ${$variableName};
                                                
                                                preg_match_all(
                                                    '/\[("|\')?([^"\'\]]+)("|\')?]/',
                                                    $variableString,
                                                    $matchesArray
                                                );
                                                
                                                if (isset($matchesArray[2]) && !empty($matchesArray[2])) {
                                                    foreach ($matchesArray[2] as $key) {
                                                        if (!isset($valueReference)
                                                            && isset($variableReference[$key])) {
                                                            $valueReference =& $variableReference[$key];
                                                        } elseif (isset($valueReference) && is_array($valueReference)) {
                                                            $valueReference =& $valueReference[$key];
                                                        }
                                                    }
                                                } else {
                                                    $valueReference = $variableReference;
                                                }
                                                
                                                if (isset($valueReference) && $valueReference !== '') {
                                                    // run filter
                                                    $variableCopy   = $valueReference;
                                                    $valueReference = call_user_func($function, $valueReference);
                                                    if ($variableCopy != $valueReference) {
                                                        $this->log(
                                                            'Die Regel "' . $filterName
                                                            . '" hat einen unerwarteten Variablenwert erkannt und erfolgreich gefiltert.',
                                                            'security',
                                                            $dataArray['severity']
                                                        );
                                                        if (is_array($variableCopy) || is_object($variableCopy)) {
                                                            $this->log(
                                                                "unerwarteter Variablenwert\r\nFilterregel: "
                                                                . $filterName
                                                                . "\r\nVariable: $$variableString\rnvorher: " . print_r(
                                                                    $variableCopy,
                                                                    true
                                                                ) . "\r\nnachher: " . print_r($valueReference, true),
                                                                'security_debug',
                                                                $dataArray['severity']
                                                            );
                                                        } else {
                                                            $this->log(
                                                                "unerwarteter Variablenwert\r\nFilterregel: "
                                                                . $filterName
                                                                . "\r\nVariable: $$variableString\r\nvorher: "
                                                                . $variableCopy . "\r\nnachher: " . $valueReference,
                                                                'security_debug',
                                                                $dataArray['severity']
                                                            );
                                                        }
                                                    }
                                                }
                                                
                                                if (isset($valueReference)) {
                                                    unset($valueReference);
                                                }
                                            }
                                        }
                                    } else {
                                        // todo
                                        $this->log('filter variables are missing', 'gprotector_error', 'error');
                                    }
                                } else {
                                    // todo
                                    $this->log(
                                        'filter function "' . $function . '" does not exist',
                                        'gprotector_error',
                                        'error'
                                    );
                                }
                            } else {
                                // todo
                                $this->log('filter function is not set', 'gprotector_error', 'error');
                            }
                        }
                    }
                } else {
                    // todo
                    $this->log('filter data is missing', 'gprotector_error', 'error');
                }
            }
        } else {
            // todo
            $this->log('v_filter_array is not set', 'gprotector_error', 'error');
        }
        
        return true;
    }
    
}
