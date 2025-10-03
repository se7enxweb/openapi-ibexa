<?php

declare(strict_types=1);

namespace Netgen\Bundle\OpenApiIbexaBundle\Controller;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Netgen\IbexaSiteApi\API\Values\Location;
use Netgen\OpenApiIbexa\Page\LocationList;
use Netgen\OpenApiIbexa\Page\Output\OutputVisitor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_encode;
use function max;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

final class LocationChildren extends Controller
{
    public function __construct(
        private ConfigResolverInterface $configResolver,
        private ResponseTagger $responseTagger,
        private OutputVisitor $outputVisitor,
        private int $defaultLimit,
    ) {}

    public function __invoke(Request $request, Location $location, int $maxPerPage, int $currentPage): JsonResponse
    {
        $currentPage = max($currentPage, 1);

        if ($maxPerPage < 1) {
            $maxPerPage = $this->defaultLimit;
        }

        $children = $location->filterChildren(
            $request->query->all('contentTypes'),
            $maxPerPage,
            $currentPage,
        );

        $data = $this->outputVisitor->visit(new LocationList($children));

        $response = new JsonResponse(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            Response::HTTP_OK,
            [],
            true,
        );

        $this->configureCache($this->configResolver, $response);

        $this->responseTagger->tag($location->innerLocation);
        $this->responseTagger->tag($location->contentInfo->innerContentInfo);

        return $response;
    }
}
