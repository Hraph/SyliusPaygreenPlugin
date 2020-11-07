<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Types;


class TransactionStatus
{
    public const STATUS_SUCCEEDED = "SUCCESSED"; // Paygreen API are using wrong spelling
    public const STATUS_REFUSED = "REFUSED";
    public const STATUS_CANCELLED = "CANCELLED";
    public const STATUS_PENDING = "PENDING";
    public const STATUS_REFUNDED = "REFUNDED";
}
