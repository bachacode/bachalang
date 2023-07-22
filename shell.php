<?php

declare(strict_types=1);

require_once('./constants.php');
require_once('./autoloader.php');

use Bachalang\Runner;

$runner = new Runner();

while (true) {
    $text = readline('bachalang > ');
    $result = $runner->run($text);
    echo $result . PHP_EOL;
}
