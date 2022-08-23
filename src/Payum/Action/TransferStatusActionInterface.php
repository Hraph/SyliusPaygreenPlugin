<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;

interface TransferStatusActionInterface extends ActionInterface, ApiAwareInterface, GatewayAwareInterface
{

}
