<?php
function validateName(string $name): bool
{
    $name = trim($name);
    if (mb_strlen($name) < 2 || (mb_strlen($name)) > 50) {
        return false;
    }
    return preg_match('/^[a-zа-я\s]+$/iu', $name);
}

function validateDate(string $date): bool
{
    $dateBlocks = explode("-", $date);

    if (count($dateBlocks) < 3) {
        return false;
    }

    foreach ($dateBlocks as $num) {
        if (!is_numeric($num) || str_contains($num, '.')) {
            return false;
        }
    }

    if ($dateBlocks[0] < 1 || $dateBlocks[0] > 31) {
        return false;
    }
    if ($dateBlocks[1] < 1 || $dateBlocks[1] > 12) {
        return false;
    }
    if ($dateBlocks[2] < 1900 || $dateBlocks[2] > date('Y')) {
        return false;
    }

    return true;
}

function rewriteFileWithoutLine(int $lineIndex, $fileAddress): string
{
    $linesArray = file($fileAddress);
    $lineAsItIs = $linesArray[$lineIndex];
    unset($linesArray[$lineIndex]);
    $file = fopen($fileAddress, 'w');
    fwrite($file, join($linesArray));
    return $lineAsItIs;
}