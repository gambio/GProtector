<?php
/* --------------------------------------------------------------
  Pattern.php 2026-09-25
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2026 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \InvalidArgumentException;

class Pattern
{
    /**
     * @var string $pattern
     */
    private $pattern;
    
    
    /**
     * Pattern constructor.
     *
     * @param mixed $pattern PCRE pattern including delimiters, e.g. "#^Foo(?:\W|$)#"
     */
    public function __construct($pattern)
    {
        $this->validatePattern($pattern);
        $this->pattern = $pattern;
    }
    
    
    /**
     * Getter for pattern
     *
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }
    
    
    /**
     * Returns true if the value, or any scalar inside an array value, matches the pattern.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function matches($value)
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->matches($item)) {
                    return true;
                }
            }
            
            return false;
        }
        
        return is_scalar($value) && preg_match($this->pattern, (string)$value) === 1;
    }
    
    
    /**
     * Validates pattern
     *
     * @param mixed $pattern The pattern to validate
     *
     * @throws InvalidArgumentException if the pattern is not a string or does not compile
     */
    private function validatePattern($pattern)
    {
        if (!is_string($pattern) || @preg_match($pattern, '') === false) {
            throw new InvalidArgumentException('Invalid $pattern');
        }
    }
}
