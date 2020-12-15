<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Exception;


class PaygreenException extends \Exception
{
    const CODE_PAYUM = 10;
    const CODE_INSERT = 100;
    const CODE_UPDATE = 200;
    const CODE_DELETE = 300;
    const CODE_FIND = 400;
    const CODE_FIND_ALL = 500;
}
