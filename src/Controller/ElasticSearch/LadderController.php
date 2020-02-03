<?php

namespace App\Controller\ElasticSearch;

use App\Controller\APIController;
use App\Fetcher\LadderFetcher;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @NamePrefix("es.")
 */
class LadderController extends APIController
{
    /**
     * @Get(path="/ladder")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=50, nullable=true)
     * @QueryParam(name="positions", nullable=true, map=true)
     * @QueryParam(name="country", nullable=true)
     * @QueryParam(name="region", nullable=true)
     * @QueryParam(name="team", nullable=true)
     * @QueryParam(name="sort", nullable=true)
     * @QueryParam(name="order", nullable=true)
     */
    public function getLadderAction(ParamFetcher $paramFetcher, LadderFetcher $ladderFetcher): JsonResponse
    {
        $options = [
            'positions' => $paramFetcher->get('positions') ?? [],
            'country' => $paramFetcher->get('country'),
            'region' => $paramFetcher->get('region'),
            'team' => $paramFetcher->get('team'),
            'sort' => $paramFetcher->get('sort'),
        ];

        if ($paramFetcher->get('page')) {
            $options['page'] = (int) $paramFetcher->get('page');
        }
        if ($paramFetcher->get('per_page')) {
            $options['per_page'] = (int) $paramFetcher->get('per_page');
        }
        if ($paramFetcher->get('order')) {
            $options['order'] = $paramFetcher->get('order');
        }

        $players = $ladderFetcher->fetchByPage($options);

        return new JsonResponse($players);
    }
}
