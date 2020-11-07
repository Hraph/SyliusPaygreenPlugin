<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Helper;


interface ConvertRefundDataInterface
{
    public function convert(array $data, string $currency): array;
}
