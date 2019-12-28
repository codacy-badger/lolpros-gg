<?php

namespace App\Controller\ElasticSearch;

use App\Builder\PlayerBuilder;
use App\Controller\APIController;
use Elastica\Exception\NotFoundException;
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
    public function getPlayerUuidAction(string $uuid, PlayerBuilder $playersBuilder): JsonResponse
    {
        try {
            $player = $playersBuilder->build(['uuid' => $uuid]);

            return new JsonResponse($player);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Get(path="/players/{slug}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getPlayerSlugAction(string $slug, PlayerBuilder $playersBuilder): JsonResponse
    {
        try {
            $player = $playersBuilder->build(['slug' => $slug]);

            return new JsonResponse($player);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }
}
