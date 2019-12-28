<?php

namespace App\Controller\ElasticSearch;

use App\Builder\TeamBuilder;
use App\Controller\APIController;
use App\Fetcher\TeamFetcher;
use Elastica\Exception\NotFoundException;
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
class TeamsController extends APIController
{
    /**
     * @Get(path="/teams")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=50, nullable=true)
     * @QueryParam(name="active", nullable=true)
     * @QueryParam(name="query", nullable=true)
     */
    public function getTeamsAction(ParamFetcherInterface $paramFetcher, TeamFetcher $teamsFetcher): JsonResponse
    {
        $options = [
            'active' => $this->getNullOrBoolean($paramFetcher->get('active')),
            'query' => $paramFetcher->get('query'),
        ];

        if ($paramFetcher->get('page')) {
            $options['page'] = (int) $paramFetcher->get('page');
        }
        if ($paramFetcher->get('per_page')) {
            $options['per_page'] = (int) $paramFetcher->get('per_page');
        }

        $teams = $teamsFetcher->fetchByPage($options);

        return new JsonResponse($teams);
    }

    /**
     * @Get(path="/teams/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getTeamsUuidAction(string $slug, TeamBuilder $teamBuilder): JsonResponse
    {
        try {
            $team = $teamBuilder->build(['$slug' => $slug]);

            return new JsonResponse($team);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Get(path="/teams/{slug}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getTeamSlugAction(string $slug, TeamBuilder $teamBuilder): JsonResponse
    {
        try {
            $team = $teamBuilder->build(['slug' => $slug]);

            return new JsonResponse($team);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }
}
