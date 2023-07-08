<?php

require './bachalang.php';

while (true) {
    $text = readline('bachalang > ');
    $result = run($text);

    print_r($result);
}
