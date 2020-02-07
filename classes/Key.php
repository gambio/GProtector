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

use\InvalidArgumentException;

class Key
{
    /**
     * @var  string $key
     */
    private $key;
    
    
    public function __construct($key)
    {
        $this->validateKey($key);
        $this->key = $key;
    }
    
    
    /**
     * Validates key.
     *
     * @param mixed $key The key to validate
     *
     * @throws InvalidArgumentException if key is null or not string
     *
     */
    private function validateKey($key)
    {
        if ($key === null || (is_string($key)) === false) {
            throw new InvalidArgumentException('Invalid $key');
        }
    }
    
    
    /**
     * Getter for key
     *
     * @return string
     */
    
    public function key()
    {
        return $this->key;
    }
    
}