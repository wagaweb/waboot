<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\DB;
use Waboot\inc\core\DBException;
use Waboot\inc\core\DBUnavailableDependencyException;

class OrderSimulatorCartItemException extends \Exception
{
}