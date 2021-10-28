<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Types;


class TransferStatus
{
    public const STATUS_SUCCEEDED = "SUCCESS";
    public const STATUS_CANCELLED = "CANCEL";
    public const STATUS_PENDING = "WAITING";
    public const STATUS_FAILED = "ERROR";
}
