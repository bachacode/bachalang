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

    public static function firstLevelVarDump($obj, $newLineCharacter = null)
    {
        //Decide which new Line Character we use (Based on Loïc suggestion)
        if ($newLineCharacter === null) {
            $newLineCharacter = php_sapi_name() == 'cli' ? PHP_EOL : '<br/>';
        }
        //Get all visible Items
        $data = get_object_vars($obj);

        //Loop through each Item
        foreach ($data as $key => $item) {
            //Display Key + Type
            echo $key . ' => ' . gettype($item);

            //Extract Details, beased on the Type
            if (is_string($item)) {
                echo '(' . strlen($item) . ') "' . $item . '"';
            } elseif (is_bool($item)) {
                echo '(' . ($item ? 'true' : 'false') . ')';
            } elseif (is_integer($item) || is_float($item)) {
                echo '(' . $item . ')';
            } elseif (is_object($item)) {
                echo '(' . get_class($item) . ')';
            }

            //Line Break
            echo $newLineCharacter;
        }
    }

    public static function get_class_name($className)
    {
        if ($pos = strrpos($className, '\\')) return substr($className, $pos + 1);
        return $pos;
    }
}
