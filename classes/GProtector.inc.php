<?php
/* --------------------------------------------------------------
  GProtector.inc.php 2019-06-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

class GProtector
{
	private $secureToken        = '';
	private $filterArray        = array();
	private $logHeaderTemplate  = '';
	private $separator          = "\r\n";
	private $logConnectorsArray = array();
    
    /**
     * @var GProtectorFilterReader
     */
	private $filterReader;
	
	public function __construct()
	{
		$this->setSecureToken();
		$this->setLogHeaderTemplate("===========================================================\nIP: {IP}\nDatum: {DATETIME}\nScript: {SCRIPT}\nNachricht: {MESSAGE}\n\n");
		$this->initLogConnectors();
		$this->loadFunctions();
	}
    
    
    /**
     * This function starts the GProtector filters
     */
    public function start()
    {
        $filters = $this->readFilterFiles();
        $this->applyFilters($filters);
        $this->blockForbiddenIps();
	}
    
    
    /**
     * @param GProtectorFilterCollection $filters
     */
    private function applyFilters(GProtectorFilterCollection $filters)
    {
        $this->addFilters($filters);
        $this->filter();
    }
    
    
    /**
     * @param GProtectorFilterCollection $filters
     */
    private function addFilters(GProtectorFilterCollection $filters)
    {
        foreach($filters as $filter)
        {
            $this->addFilter(
                $filter->key(),
                $filter->scriptName(),
                $filter->variables(),
                $filter->function(),
                $filter->severity());
        }
    }
    
    
    /**
     * @return GProtectorFilterCollection
     */
    private function readFilterFiles()
    {
        $filterArray = $this->filterReader()->getDefaultFilterRules() + $this->filterReader()->getCustomFilterRules();
        
        return new GProtectorFilterCollection($filterArray);
    }
    
    
    /**
     * @return GProtectorFilterReader
     */
    private function filterReader()
    {
        if(!isset($this->filterReader)) {
            $this->filterReader = new GProtectorFilterReader();
        }
        
        return $this->filterReader;
    }
	
	
    /**
     *
     */
    private function blockForbiddenIps()
    {
        if($this->searchIpInBlacklist($this->getUserIp()) === true)
        {
            $this->blockIp();
        }
	}
	
	
	/*public function start()
	{
		$t_files_array = glob(GAMBIO_PROTECTOR_FILTER_DIR . $this->get_file_pattern());
		
		if(is_array($t_files_array))
		{
			foreach($t_files_array as $t_filepath)
			{
				include($t_filepath);
			}
			
			$this->filter();
		}
		else
		{
			// todo
			$this->log('no filters found', 'gprotector_error', 'warning');
		}
		
		$t_ip_blocker_result = $this->searchIpInBlacklist($this->getUserIp());
		if($t_ip_blocker_result)
		{
			$this->blockIp();
		}
		
		return true;
	}*/


	private function initLogConnectors()
	{
		$tFilesArray = glob(GAMBIO_PROTECTOR_CONNECTORS_DIR . $this->getFilePattern());

		if(is_array($tFilesArray))
		{
			foreach($tFilesArray as $tFile)
			{
				include_once($tFile);
			}
		}
	}
	
	
	/**
	 * Return the visitor's IP address
	 * 
	 * @return string Visitor's IP address
	 */
	private function getUserIp()
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$tIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$tIp = $_SERVER['HTTP_CLIENT_IP'];
		}
		else
		{
			$tIp = $_SERVER['REMOTE_ADDR'];
		}
		
		return $tIp;
	}
	
		
	/**
	 * Search the given IP in blacklist and returns true if it is in the blacklist. 
	 * 
	 * @param string $userIp User IP to check
	 *
	 * @return bool OK:false | blocked IP: true
	 * 
	 */
	private function searchIpInBlacklist($userIp)
	{
		if(file_exists($this->getIpBlacklistPath()))
		{
			if(is_readable($this->getIpBlacklistPath()))
			{
				$tFileHandle = fopen($this->getIpBlacklistPath(), 'r');
				while(!feof($tFileHandle))
				{
					$tBlockedIp = fgets($tFileHandle);
					$tBlockedIp = trim($tBlockedIp);
					
					if(!empty($tBlockedIp))
					{
						if(strpos(trim($userIp), trim($tBlockedIp)) === 0)
						{
							fclose($tFileHandle);
							return true;
						}
					}
				}
				fclose($tFileHandle);
				return false;

			}
			else
			{
				$this->log('Can not read IP-blacklist', 'gprotector_error', 'error');
			}
		}
		return false;
	}
	
	
	/**
	 * Sends 403 Header to any blocked IPs
	 */
	private function blockIp()
	{
		header("HTTP/1.0 403 forbidden");
		echo 'forbidden';
		exit;
	}


	private function addFilter($key, $scriptName, $variables, $function, $severity = 'error')
	{
		$tVariables = $variables;
		
		if(!is_array($tVariables))
		{
			$tVariables = array($tVariables);
		}
		
		$tScriptPathArray = $scriptName;
		
		if(!is_array($tScriptPathArray))
		{
			$tScriptPathArray = array($scriptName);
		}
        
        $this->filterArray[$key] = array('script_name_array' => $tScriptPathArray,
                                         'variables_array'   => $tVariables,
                                         'function'          => $function,
                                         'severity'          => $severity);
	}


	private function filter()
	{
		if(is_array($this->filterArray))
		{
			foreach($this->filterArray as $tFilterName => $tDataArray)
			{
				if(isset($valueReference))
				{
					unset($valueReference);
				}
			    
				if(is_array($tDataArray) && isset($tDataArray['script_name_array']) && is_array($tDataArray['script_name_array']))
				{
					foreach($tDataArray['script_name_array'] as $tScriptPath)
					{
						if($this->isScript($tScriptPath) === true)
						{
							if(isset($tDataArray['function']))
							{
								$cFunction = (string)$tDataArray['function'];
								$tFunctionPrefix = $this->getFunctionPrefix();
								$cFunction = $tFunctionPrefix . $cFunction;

								if(function_exists($cFunction))
								{
									if(isset($tDataArray['variables_array']) && is_array($tDataArray['variables_array']))
									{
										foreach($tDataArray['variables_array'] as $tVariable)
										{
											$cVariableString = (string)$tVariable;

											$tArrayBracketPos = (int)strpos($cVariableString, '[');
											$tVariableNameEndPos = strlen($cVariableString);
											
											if($tArrayBracketPos > 0)
											{
												$tVariableNameEndPos = $tArrayBracketPos;
											}

											if($tVariableNameEndPos > 0)
											{
												$tVariableName = substr($cVariableString, 0, $tVariableNameEndPos);

												global ${$tVariableName};

												$tVariableReference =& ${$tVariableName};

												preg_match_all('/\[("|\')?([^"\'\]]+)("|\')?]/', $cVariableString, $tMatchesArray);

												if(isset($tMatchesArray[2]) && !empty($tMatchesArray[2]))
												{
													foreach($tMatchesArray[2] as $key)
													{
														if(!isset($valueReference) && isset($tVariableReference[$key]))
														{
															$valueReference =& $tVariableReference[$key];
														}
														elseif(isset($valueReference) && is_array($valueReference))
														{
															$valueReference =& $valueReference[$key];
														}
													}
												}
												else
												{
													$valueReference = $tVariableReference;
												}

												if(isset($valueReference) && $valueReference !== '')
												{
                                                    // run filter
                                                    $tVariableCopy = $valueReference;
                                                    $valueReference = call_user_func($cFunction, $valueReference);
                                                    if($tVariableCopy != $valueReference)
                                                    {
                                                        $this->log('Die Regel "' . $tFilterName . '" hat einen unerwarteten Variablenwert erkannt und erfolgreich gefiltert.', 'security', $tDataArray['severity']);
                                                        if(is_array($tVariableCopy) || is_object($tVariableCopy))
                                                        {
                                                            $this->log("unerwarteter Variablenwert\r\nFilterregel: " . $tFilterName . "\r\nVariable: $$cVariableString\rnvorher: " . print_r($tVariableCopy, true) . "\r\nnachher: " . print_r($valueReference, true), 'security_debug', $tDataArray['severity']);
                                                        }
                                                        else
                                                        {
                                                            $this->log("unerwarteter Variablenwert\r\nFilterregel: " . $tFilterName . "\r\nVariable: $$cVariableString\r\nvorher: " . $tVariableCopy . "\r\nnachher: " . $valueReference, 'security_debug', $tDataArray['severity']);
                                                        }
                                                    }
												}
												
												if(isset($valueReference))
												{
													unset($valueReference);
												}
											}
										}							
									}
									else
									{
										// todo 
										$this->log('filter variables are missing', 'gprotector_error', 'error');
									}
								}
								else
								{
									// todo 
									$this->log('filter function "' . $cFunction . '" does not exist', 'gprotector_error', 'error');
								}
							}
							else
							{
								// todo 
								$this->log('filter function is not set', 'gprotector_error', 'error');
							}
						}
					}						
				}
				else
				{
					// todo 
					$this->log('filter data is missing', 'gprotector_error', 'error');
				}
			}
		}
		else
		{
			// todo 
			$this->log('v_filter_array is not set', 'gprotector_error', 'error');
		}
		
		return true;
	}


	private function loadFunctions()
	{
		$tFilesArray = glob(GAMBIO_PROTECTOR_FUNCTIONS_DIR . $this->getFilePattern());
		
		if(is_array($tFilesArray))
		{
			foreach($tFilesArray as $tFilepath)
			{
				include_once($tFilepath);
			}
			
			return true;
		}
		else
		{
			// todo
			$this->log('No functions found', 'gprotector_error', 'warning');
		}
		
		return false;		
	}


	private function getFilePattern()
	{
		$tFilePattern = '*.json';
		if(defined('GAMBIO_PROTECTOR_FILE_PATTERN'))
		{
			$tPattern = trim((string)GAMBIO_PROTECTOR_FILE_PATTERN);
			if($tPattern != '')
			{
				$tFilePattern = $tPattern;
			}
		}
		
		return $tFilePattern;
	}


	private function getRunningScriptPath()
	{
		$tScriptPath = false;
		
		$tBacktraceArray = debug_backtrace();
		if(is_array($tBacktraceArray))
		{
			$tRunningScriptDataArray = array_pop($tBacktraceArray);
			$tScriptPath = $tRunningScriptDataArray['file'];
			
			if(defined('GAMBIO_PROTECTOR_BASE_DIR') && is_string(GAMBIO_PROTECTOR_BASE_DIR))
			{
				$tScriptPath = str_replace(GAMBIO_PROTECTOR_BASE_DIR, '', $tScriptPath);
			}
		}
		
		if($tScriptPath === false)
		{
			// todo
			$this->log('script name could not be determined', 'gprotector_error', 'warning');
		}	
		
		
		return $tScriptPath;
	}


	private function getFunctionPrefix()
	{
		$tFunctionPrefix = 'gprotector_';
		
		if(defined('GAMBIO_PROTECTOR_FUNCTION_PREFIX'))
		{
			$tPrefix = preg_replace('/[^a-zA-Z_]/', '', trim((string)GAMBIO_PROTECTOR_FUNCTION_PREFIX));
			if($tPrefix != '')
			{
				$tFunctionPrefix = $tPrefix;
			}
		}
		
		return $tFunctionPrefix;
	}


	private function getTokenPrefix()
	{
		$tTokenPrefix = 'gprotector_';
		
		if(defined('GAMBIO_PROTECTOR_TOKEN_FILE_PREFIX'))
		{
			$tPrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', trim((string)GAMBIO_PROTECTOR_TOKEN_FILE_PREFIX));
			if($tPrefix != '')
			{
				$tTokenPrefix = $tPrefix;
			}
		}
		
		return $tTokenPrefix;
	}


	private function setSecureToken()
	{
		$tFilesArray = glob(GAMBIO_PROTECTOR_TOKEN_DIR . $this->getTokenPrefix() . '*');

		if(is_array($tFilesArray))
		{
			foreach($tFilesArray as $tFilepath)
			{
				$tTokenFilename = basename($tFilepath);
				$tToken = str_replace($this->getTokenPrefix(), '', $tTokenFilename);

				if(strlen($tToken) > 0)
				{
					$this->secureToken = $tToken;
				}				
			}
		}
		elseif(is_writable(GAMBIO_PROTECTOR_TOKEN_DIR))
		{
			$tToken = md5(time() . rand());
			$tTokenFile = GAMBIO_PROTECTOR_TOKEN_DIR . $this->getTokenPrefix() . $tToken;

			if(function_exists('file_put_contents'))
			{
				@file_put_contents($tTokenFile, 'empty');
			}
			else
			{
				$fp = @fopen($tTokenFile, 'w');
				@fwrite($fp, 'empty');
				@fclose($fp);
			}

			if(!file_exists($tTokenFile))
			{
				return false;
			}
			else
			{
				$this->secureToken = $tToken;
			}
		}

		return true;
	}


	private function getSecureToken()
	{
		return preg_replace('/[^a-zA-Z0-9_-]/', '', (trim((string)$this->secureToken)));
	}


	private function writeCustomLog($pMessage, $pType, $pSeverity = 'error')
	{
		$cMessage = (string)$pMessage;
		$tMessageDetails = $this->prepareLogMessage($cMessage);
		
		if(strpos($cMessage, $this->separator) !== false)
		{
			$cMessage = substr($cMessage, 0, strpos($cMessage, $this->separator));
		}
		
		$logSuccess = 1;

		foreach($this->logConnectorsArray as $cooGProtectorLogConnector)
		{
			$tErrorType = 'GPROTECTOR ' . strtoupper($pSeverity);
			$logSuccess &= $cooGProtectorLogConnector->log($cMessage, 'security', $pType, $pSeverity, $tErrorType, $tMessageDetails);
		}
		
		if(!$logSuccess)
		{
			$this->writeLog($pMessage, $pType, $pSeverity);
		}
		
		return true;
	}


	private function prepareLogMessage($string)
	{
		$cString = (string)$string;
		$tPreparedMessage = '';

		if(strpos($cString, $this->separator) !== false)
		{ 
			$tPreparedMessage = str_replace("'", "\\'", substr($cString, strpos($cString, $this->separator) + strlen($this->separator)));
		}
		
		return $tPreparedMessage;
	}


	private function writeLog($message, $type, $severity = 'error')
	{
		$cMessage = (string)$message;
		$cLogFilename = $this->getLogFilename($type);
		if($cLogFilename !== false)
		{
			$tLogFilePath = GAMBIO_PROTECTOR_LOG_DIR . $cLogFilename;
			$tWrittenBytes = false;

			if(is_dir(GAMBIO_PROTECTOR_LOG_DIR)
			   && is_writable(GAMBIO_PROTECTOR_LOG_DIR)
			   && ((	file_exists($tLogFilePath)
						&& is_writeable($tLogFilePath)
				   )
				   || (!file_exists($tLogFilePath)))
			)
			{
				if(function_exists('file_put_contents'))
				{
					$tWrittenBytes = @file_put_contents($tLogFilePath, $this->getSubstitutedLogContent($this->logHeaderTemplate, $cMessage, $severity), FILE_APPEND | LOCK_EX);
				}
				else
				{
					$fp = @fopen($tLogFilePath, 'a');
					$tWrittenBytes = @fwrite($fp, $this->getSubstitutedLogContent($this->logHeaderTemplate, $cMessage, $severity));
					@fclose($fp);
				}

				if((defined('GAMBIO_PROTECTOR_GZIP_LOG') && GAMBIO_PROTECTOR_GZIP_LOG === true) || defined('GAMBIO_PROTECTOR_GZIP_LOG') === false)
				{
					$tMaxFilesize = 1 * 1024 * 1024; // standard: 1 megabyte
					if(defined('GAMBIO_PROTECTOR_LOG_MAX_FILESIZE') && (double)GAMBIO_PROTECTOR_LOG_MAX_FILESIZE > 0)
					{
						$tMaxFilesize = (double)GAMBIO_PROTECTOR_LOG_MAX_FILESIZE * 1024 * 1024;
					}

					// compress logfile if larger than GAMBIO_PROTECTOR_LOG_MAX_FILESIZE megabyte
					if(filesize($tLogFilePath) > $tMaxFilesize)
					{
						$fp = @fopen($tLogFilePath, 'r+');
						if($fp !== false)
						{
							@date_default_timezone_set('Europe/Berlin');
							$tCompressedFilePath = substr($tLogFilePath, 0, strpos($tLogFilePath, ".")) . '-' . date('Ymd_His') . '.log.gz';
							$tCompressedFile = @gzopen($tCompressedFilePath, 'w9');
							if($tCompressedFile !== false)
							{
								@gzwrite($tCompressedFile, fread($fp, filesize($tLogFilePath)));
								@gzclose($tCompressedFile);

								// delete content of log which was compressed before
								@ftruncate($fp, 0);
							}
							@fclose($fp);
						}
					}
				}
			}

			if($tWrittenBytes === false || $tWrittenBytes == 0)
			{
				return false;
			}

			return true;
		}

		return false;
	}


	private function log($message, $type, $severity = 'error')
	{
		if(!empty($this->logConnectorsArray))
		{
			return $this->writeCustomLog($message, $type, $severity);
		}

		return $this->writeLog($message, $type, $severity);
	}


	private function getLogFilename($type)
	{
		$cType = basename(trim((string)$type));
		$tSecureToken = $this->getSecureToken();
		
		if($cType != '' && $tSecureToken != '')
		{
			$tLogFilename = $cType . '-' . $tSecureToken . '.log';
			return $tLogFilename;
		}
		
		return false;		
	}


	private function setLogHeaderTemplate($template)
	{
		$cTemplate              = (string)$template;
		$this->logHeaderTemplate = $cTemplate;
	}


	private function getSubstitutedLogContent($template, $message = '', $severity = 'error', $logFilename = 'security', $messageDetails = '')
	{
		@date_default_timezone_set('Europe/Berlin');
		
		$cTemplate = (string)$template;
		$cMessage = (string)$message;
		$cSeverity = (string)$severity;
		$cLogFilename = (string)$logFilename;
		$cMessageDetails = (string)$messageDetails;
		
		$cTemplate = $this->substitute($cTemplate, '{IP}', $this->getUserIp());
		$cTemplate = $this->substitute($cTemplate, '{DATETIME}', date('Y-m-d H:i:s'));
		$cTemplate = $this->substitute($cTemplate, '{MESSAGE}', $cMessage);
		$cTemplate = $this->substitute($cTemplate, '{SCRIPT}', $this->getRunningScriptPath());
		$cTemplate = $this->substitute($cTemplate, '{LOGFILE_NAME}', (string)$cLogFilename);
		$cTemplate = $this->substitute($cTemplate, '{SEVERITY}', $cSeverity);
		$cTemplate = $this->substitute($cTemplate, '{ERROR_TYPE}', 'GPROTECTOR ' . strtoupper($severity));
		$cTemplate = $this->substitute($cTemplate, '{MESSAGE_DETAILS}', $cMessageDetails);
		
		return $cTemplate;
	}


	private function substitute($content, $placeHolder, $substitution = '')
	{
		$tContent = (string)$content;
		
		if(strpos($content, $placeHolder) !== false)
		{
			$tContent = str_replace((string)$placeHolder, (string)$substitution, $tContent);
		}
		
		return $tContent;
	}


	private function isScript($scriptPath)
	{
		$cScriptPath = (string)$scriptPath;
		if($this->getRunningScriptPath() == $cScriptPath)
		{
			return true;
		}
		
		return false;	
	}


	private function getIpBlacklistPath()
	{
		$tDir = dirname(__FILE__) . '/';
		
		if(defined('GAMBIO_PROTECTOR_DIR') && is_string(GAMBIO_PROTECTOR_DIR) && @is_dir(GAMBIO_PROTECTOR_DIR))
		{
			$tDir = GAMBIO_PROTECTOR_DIR;
		}
		
		return $tDir . 'ip_blacklist.txt';
	}
}
