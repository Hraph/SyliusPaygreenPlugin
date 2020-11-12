<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;

interface BaseRenderableActionInterface extends ActionInterface, ApiAwareInterface, GatewayAwareInterface
{

}
