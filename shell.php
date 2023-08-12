<?php

declare(strict_types=1);

require './constants.php';
require 'vendor/autoload.php';

use Bachalang\Runner;

$runner = new Runner();

while (true) {
    $text = readline('bachalang > ');
    $result = $runner->run($text);
    if(!is_null($result)) {
        echo $result . PHP_EOL;
    }
}
