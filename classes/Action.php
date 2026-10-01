<?php
/* --------------------------------------------------------------
  Action.php 2026-09-25
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2026 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \InvalidArgumentException;

class Action
{
    public const SANITIZE = 'sanitize';
    public const DENY     = 'deny';
    
    /**
     * @var string $action
     */
    private $action;
    
    
    /**
     * Action constructor.
     *
     * @param mixed $action
     */
    public function __construct($action = self::SANITIZE)
    {
        $this->validateAction($action);
        $this->action = $action;
    }
    
    
    /**
     * Getter for action
     *
     * @return string
     */
    public function action()
    {
        return $this->action;
    }
    
    
    /**
     * Validates action
     *
     * @param mixed $action The action to validate
     *
     * @throws InvalidArgumentException
     */
    private function validateAction($action)
    {
        if (!in_array($action, [self::SANITIZE, self::DENY], true)) {
            throw new InvalidArgumentException('Invalid $action');
        }
    }
}
