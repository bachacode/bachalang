<?php

declare(strict_types=1);

require './constants.php';
require 'vendor/autoload.php';

use Bachalang\Runner;

$runner = new Runner();

while (true) {
    $text = readline('bachalang > ');
    if (trim($text) == "") {
        continue;
    }
    $result = $runner->run($text);

    if(!is_null($result)) {
        if (count($result->elements) == 1) {
            echo $result->elements[0] . PHP_EOL;
        } else {
            echo $result . PHP_EOL;
        }
    }
}
