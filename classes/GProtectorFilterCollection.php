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

class FilterCollection implements IteratorAggregate
{
    /**
     * @var array
     */
    private $gprotectorFilterArray = [];
    
    
    /**
     * Initialize the collection instance.
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
     * Add a new item.
     *
     * @param GProtectorFilter $item Item which should add to the collection
     *
     */
    public function add(GProtectorFilter $item)
    {
        $this->gprotectorFilterArray[] = $item;
    }
    
    
    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->gprotectorFilterArray);
    }
}