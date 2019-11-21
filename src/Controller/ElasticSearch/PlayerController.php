<?php

namespace App\Controller\ElasticSearch;

use App\Builder\PlayersBuilder;
use App\Controller\APIController;
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
    public function getPlayerUuidAction(string $uuid, PlayersBuilder $playersBuilder): JsonResponse
    {
        $player = $playersBuilder->build(['uuid' => $uuid]);

        if (!$player) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($player);
    }

    /**
     * @Get(path="/players/{slug}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getPlayerSlugAction(string $slug, PlayersBuilder $playersBuilder): JsonResponse
    {
        $player = $playersBuilder->build(['slug' => $slug]);

        if (!$player) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($player);
    }
}
