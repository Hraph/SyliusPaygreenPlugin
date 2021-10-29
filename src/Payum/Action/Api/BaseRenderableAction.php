<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;

use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use Psr\Log\LoggerInterface;

abstract class BaseRenderableAction extends BaseApiGatewayAwareAction implements BaseRenderableActionInterface
{
    use GatewayAwareTrait;

    private bool $useInsiteMode;

    public function __construct(bool $useInsiteMode, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->useInsiteMode = $useInsiteMode;
    }

    /**
     * Render the url in insite mode
     * @param $url
     */
    protected function redirectOrRenderUrl($url){
        if (!$this->useInsiteMode) {
            throw new HttpPostRedirect($url);
        }
        else {
            $renderTemplate = new RenderTemplate('@SyliusPaygreenPlugin/Checkout/payment.html.twig', [
                'execute_url' => $url
            ]);
            $this->gateway->execute($renderTemplate);

            throw new HttpResponse($renderTemplate->getResult());
        }
    }
}
