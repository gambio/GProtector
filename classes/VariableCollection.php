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
use \ArrayIterator;
use \Traversable;

class VariableCollection implements IteratorAggregate
{
    /**
     * @var array
     */
    private $variableArray = [];
    
    
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
     * @param Variable $item Item which should be added to the collection
     *
     */
    public function add(Variable $item)
    {
        $this->variableArray[] = $item;
    }
    
    
    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->variableArray);
    }
}