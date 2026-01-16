<?php



if (! function_exists('format_currency')) {
    function format_currency(float|int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
