<?php

namespace App\Controller\ElasticSearch;

use App\Controller\APIController;
use App\Fetcher\MemberFetcher;
use App\Fetcher\RankingFetcher;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @NamePrefix("es.")
 */
class RankingsController extends APIController
{
    /**
     * @Get(path="/rankings/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=50, nullable=true)
     * @QueryParam(name="season", nullable=true)
     * @QueryParam(name="start", nullable=true)
     * @QueryParam(name="end", nullable=true)
     * @QueryParam(name="select", nullable=true)
     */
    public function getRankingsAccountAction(string $uuid, ParamFetcherInterface $paramFetcher, RankingFetcher $rankingFetcher): JsonResponse
    {
        $options = [
            'uuid' => $uuid,
            'season' => $paramFetcher->get('season'),
            'start' => $paramFetcher->get('start'),
            'end' => $paramFetcher->get('end'),
            'select' => $paramFetcher->get('select'),
        ];

        if ($paramFetcher->get('page')) {
            $options['page'] = (int) $paramFetcher->get('page');
        }
        if ($paramFetcher->get('per_page')) {
            $options['per_page'] = (int) $paramFetcher->get('per_page');
        }

        $rankings = $rankingFetcher->fetchByPage($options);

        return new JsonResponse($rankings);
    }
}
