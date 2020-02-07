<?php
/* --------------------------------------------------------------
  GProtectorFilter.php 2020-02-04
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

class Filter
{
    /**
     * @var  Key $key
     */
    private $key;
    
    /**
     * @var ScriptName $scriptName
     */
    private $scriptName;
    
    /**
     * @var VariableCollection $variables
     */
    private $variables;
    
    /**
     * @var Method $method
     */
    private $method;
    
    /**
     * @var Severity $severity
     */
    private $severity;
    
    
    /**
     * Initializes filter instance inside this class
     *
     * GProtectorFilter constructor.
     *
     * @param Key                $key
     * @param ScriptName         $scriptName
     * @param VariableCollection $variables
     * @param Method             $method
     * @param Severity           $severity
     */
    private function __construct(
        Key $key,
        ScriptName $scriptName,
        VariableCollection $variables,
        Method $method,
        Severity $severity
    ) {
        $this->key        = $key->key();
        $this->scriptName = $scriptName->scriptName();
        $this->variables  = $variables;
        $this->method     = $method->method();
        $this->severity   = $severity->severity();
    }
    
}