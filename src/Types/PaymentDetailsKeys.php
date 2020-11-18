<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Types;


class PaymentDetailsKeys
{
    public const PAYGREEN_TRANSACTION_ID = "paygreen_pid";
    public const PAYGREEN_MULTIPLE_TRANSACTION_ID = "paygreen_multiple_pid";
    public const PAYGREEN_CARDPRINT_ID = "paygreen_cardprint_pid";
    public const FACTORY_USED = "factory_used";
    public const NOTIFIED_URL = "notified_url";
    public const RETURNED_URL = "returned_url";
}
