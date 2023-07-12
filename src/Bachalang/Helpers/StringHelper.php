<?php

declare(strict_types=1);

namespace Bachalang\Helpers;

use Bachalang\Position;

class StringHelper
{
    public static function stringWithArrows(string $text, Position $posStart, ?Position $posEnd)
    {
        $result = '';

        // Calculate indices
        $substring = substr($text, 0, $posStart->index);
        $indexStart = strrpos($substring, "\n");
        if ($indexStart === false) {
            $indexStart = 0;
        }
        var_dump($indexStart) . PHP_EOL;
        $indexEnd = strpos($text, "\n", $indexStart + 1);
        if ($indexEnd < 0) {
            $indexEnd = strlen($text);
        }

        // Generate each line
        $line_count = $posEnd->line - $posStart->line + 1;
        for ($i = 0; $i < $line_count; $i++) {
            // Calculate line columns
            $line = substr($text, $indexStart, $indexEnd - $indexStart + 1);
            $colStart = ($i == 0) ? $posStart->col : 0;
            $colEnd = ($i == $line_count - 1) ? strlen($line) - 1 : $posEnd->col;

            // Append to result
            $result .= $line . "\n";
            if ($colEnd - $colStart >= 0) {
                $result .= str_repeat(' ', $colStart) . str_repeat('^', $colEnd - $colStart);
            }

            // Re-calculate indices
            $indexStart = $indexEnd;
            $indexEnd = strpos($text, "\n", $indexStart + 1);
            if ($indexEnd < 0) {
                $indexEnd = strlen($text);
            }
        }

        return str_replace("\t", '', $result);
    }
}
