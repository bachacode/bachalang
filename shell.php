<?php

declare(strict_types=1);

require_once('./constants.php');
require_once('./autoloader.php');

use Bachalang\Runner;

$runner = new Runner();

echo 5 . 5;

while (true) {
    $text = readline('bachalang > ');
    $result = $runner->run($text);
    if(!is_null($result)) {
        echo $result . PHP_EOL;
    }
}
