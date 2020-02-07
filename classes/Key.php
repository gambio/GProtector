<?php

namespace GProtector;

use\InvalidArgumentException;

class Key
{
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
     * @throws InvalidArgumentException
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