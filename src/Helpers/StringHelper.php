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
        $indexStart = max(strrpos($substring, "\n"), 0);
        if ($indexStart === false) {
            $indexStart = 0;
        }
        $indexEnd = strpos($text, "\n", $indexStart + 1);
        if ($indexEnd === false || $indexEnd < 0) {
            $indexEnd = strlen($text);
        }
        // Generate each line
        $lineCount = ($posEnd->line - $posStart->line) + 1;
        for ($i = 0; $i < $lineCount; $i++) {
            // Calculate line columns
            $line = substr($text, $indexStart, $indexEnd - $indexStart + 1);
            $colStart = ($i == 0) ? $posStart->col : 0;
            $colEnd = ($i == $lineCount - 1) ? $posEnd->col : strlen($line) - 1;

            // Append to result
            $result .= $line . PHP_EOL;
            if ($colEnd - $colStart >= 0) {
                $result .= str_repeat(' ', $colStart + 1) . str_repeat('^', $colEnd - $colStart);
            }
            // Re-calculate indices
            $indexStart = $indexEnd;
            $indexEnd = strpos($text, "\n", $indexStart);
            if ($indexEnd < 0) {
                $indexEnd = strlen($text);
            }
        }

        return str_replace("\t", '', $result);
    }

}
