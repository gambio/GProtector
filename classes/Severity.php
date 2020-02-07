<?php

/* --------------------------------------------------------------
  GProtectorFilter.php 2020-02-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \InvalidArgumentException;

class Severity
{
    /**
     * @var string $severity
     */
    private $severity;
    
    
    /**
     * Initializes severity instance
     *
     * Severity constructor.
     *
     * @param $severity
     */
    public function __construct($severity)
    {
        $this->validateSeverity($severity);
        $this->severity = $severity;
    }
    
    
    /**
     * Validates severity
     *
     * @param mixed $severity severity to be validated
     *
     * @throws InvalidArgumentException
     *
     */
    private function validateSeverity($severity)
    {
        if ($severity === null || (is_string($severity)) === false) {
            throw new InvalidArgumentException('Invalid $severity');
        }
    }
    
    
    /**
     * Getter for severity
     *
     * @return string
     */
    public function severity()
    {
        return $this->severity;
    }
}