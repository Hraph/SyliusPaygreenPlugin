<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Controller;

use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Payum\Bundle\PayumBundle\Controller\CaptureController as CaptureControllerBase;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureController extends CaptureControllerBase
{
    /**
     * Override default capture action to integrate PayGreen iframe process page instead of redirecting it
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws \Exception
     */
    public function doAction(Request $request)
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        try {
            $gateway->execute(new Capture($token));

            // Case Capture didn't Redirect
            $this->getPayum()->getHttpRequestVerifier()->invalidate($token);
            return $this->redirect($token->getAfterUrl()); // Redirect only if capture didn't
        }
        catch(HttpPostRedirect $redirect){
            // A valid redirect to PayGreen HOST is made
            $validPaygreenUrl = (false !== strpos($redirect->getUrl(), PaygreenApiClientInterface::API_HOST)) || (false !== strpos($redirect->getUrl(), PaygreenApiClientInterface::SANDBOX_API_HOST));

            if ($validPaygreenUrl)
                return $this->render('@SyliusPaygreenPlugin/Checkout/payment.html.twig', [
                    'execute_url' => $redirect->getUrl()
                ]);
            else
                return $this->redirect($redirect->getUrl());
        }
    }
}
