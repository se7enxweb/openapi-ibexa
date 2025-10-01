<?php

declare(strict_types=1);

namespace Netgen\Bundle\OpenApiIbexaBundle\Controller;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller extends AbstractController
{
    protected function configureCache(ConfigResolverInterface $configResolver, Response $response): void
    {
        if (!((bool) $configResolver->getParameter('content.view_cache'))) {
            return;
        }

        if ((bool) $configResolver->getParameter('content.ttl_cache')) {
            $response->setPublic();
            $response->setSharedMaxAge((int) $configResolver->getParameter('content.default_ttl'));
        }
    }
}
