<?php

/* --------------------------------------------------------------
  ScriptNameCollection.php 2020-02-20
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \ArrayIterator;
use \InvalidArgumentException;
use \IteratorAggregate;
use \Traversable;

class ScriptNameCollection implements IteratorAggregate
{
    /**
     * @var array
     */
    private $scriptNameArray = [];
    
    
    /**
     * Initializes the collection instance.
     *
     * @param array $items
     *
     * @throws InvalidArgumentException
     *
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            try {
                $this->add($item);
            } catch (InvalidArgumentException $e) {
                throw $e;
            }
        }
    }
    
    
    /**
     * Adds a new item.
     *
     * @param ScriptName $item Item which should be added to the collection
     *
     */
    private function add(ScriptName $item)
    {
        $this->scriptNameArray[] = $item;
    }
    
    
    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->scriptNameArray);
    }
    
    
    /**
     * Getter for variable array.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->scriptNameArray;
    }
    
    
}