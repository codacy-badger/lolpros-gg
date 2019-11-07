<?php

namespace App\Controller\ElasticSearch;

use App\Controller\APIController;
use App\Fetcher\IdentityFetcher;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @NamePrefix("es.")
 */
class PlayerController extends APIController
{
    /**
     * @Get(path="/players/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getPlayerUuidAction(string $uuid, IdentityFetcher $playersFetcher): JsonResponse
    {
        $player = $playersFetcher->fetchOne(['uuid' => $uuid]);

        if (!$player) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($player);
    }

    /**
     * @Get(path="/players/{slug}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getPlayerSlugAction(string $slug, IdentityFetcher $playersFetcher): JsonResponse
    {
        $player = $playersFetcher->fetchOne(['slug' => $slug]);

        if (!$player) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($player);
    }
}
