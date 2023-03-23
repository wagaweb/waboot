<?php

namespace Waboot\inc\core\helpers;

use Waboot\inc\core\ThemeException;

/**
 * @param string $command
 * @param $callable
 * @param string $prefix
 * @param array $description
 * @return void
 * @throws ThemeException
 */
function registerCommand(string $command, $callable, string $prefix = '', array $description = []): void {
    Waboot()->registerCommand($command,$callable,$prefix,$description);
}
