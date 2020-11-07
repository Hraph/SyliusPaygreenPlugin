<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Helper;


final class IntToStringConverter
{
    public function convertIntToString(int $value, int $divisor): string
    {
        return number_format(abs($value / $divisor), 2, '.', '');
    }
}
