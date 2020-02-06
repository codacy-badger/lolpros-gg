<?php

namespace App\Controller\ElasticSearch;

use App\Controller\APIController;
use App\Fetcher\SearchFetcher;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @NamePrefix("es.")
 */
class SearchController extends APIController
{
    /**
     * @Get(path="/search")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @QueryParam(name="query", nullable=true)
     */
    public function getSearchAction(ParamFetcherInterface $paramFetcher, SearchFetcher $searchFetcher): JsonResponse
    {
        $results = $searchFetcher->fetchByPage(['query' => $paramFetcher->get('query')]);

        return new JsonResponse($results);
    }
}
