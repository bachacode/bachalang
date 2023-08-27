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
        if(!isset($result->elements)) {
            echo $result . PHP_EOL;
        } elseif(count($result->elements) == 1) {
            echo $result->elements[0] . PHP_EOL;
        } else {
            echo (string) $result . PHP_EOL;
        }
    }
}
