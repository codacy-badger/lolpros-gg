<?php

namespace App\Controller\LeagueOfLegends\Riot;

use App\Controller\APIController;
use App\Factory\LeagueOfLegends\LoLProsFactory;
use App\Factory\LeagueOfLegends\RankingsFactory;
use App\Manager\LeagueOfLegends\Player\PlayerManager;
use App\Manager\LeagueOfLegends\Riot\RiotLeagueManager;
use App\Manager\LeagueOfLegends\Riot\RiotSpectatorManager;
use App\Manager\LeagueOfLegends\Riot\RiotSummonerManager;
use Exception;
use FOS\RestBundle\Controller\Annotations\Get;
use RiotAPI\LeagueAPI\Exceptions\RequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/look-up")
 */
class LookUpController extends APIController
{
    /**
     * @Get(path="/summoner/{name}")
     */
    public function getIdFromSummonerNameAction(string $name, RiotSummonerManager $riotSummonerManager): Response
    {
        try {
            $summoner = $riotSummonerManager->findPlayer($name);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return new JsonResponse($summoner);
    }

    /**
     * @Get(path="/id/{id}")
     */
    public function getSummonerNameFromIdAction(string $id, RiotSummonerManager $riotSummonerManager): Response
    {
        try {
            $summoner = $riotSummonerManager->getForId($id);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return new JsonResponse($summoner);
    }

    /**
     * @Get(path="/game/{name}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getGameForSummonerNameAction(
        string $name,
        RiotSummonerManager $riotSummonerManager,
        RiotSpectatorManager $riotSpectatorManager,
        RiotLeagueManager $riotLeagueManager,
        PlayerManager $playerManager
    ): Response {
        try {
            $summoner = $riotSummonerManager->findPlayer($name);
        } catch (RequestException $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        try {
            $game = $riotSpectatorManager->findGame($summoner->id);
            $summoners = $riotLeagueManager->getMultipleId($game->participants);
        } catch (RequestException $e) {
            return new JsonResponse($e->getMessage(), 406);
        }

        foreach ($game->participants as $participant) {
            $soloQ = $summoners[$participant->summonerId];
            $participant->ranking = $soloQ ? RankingsFactory::createArrayFromLeague($soloQ) : RankingsFactory::createEmptyArray();

            $lolpros = $playerManager->findWithAccount($participant->summonerId);
            $participant->lolpros = $lolpros ? LoLProsFactory::createArrayFromRiotAccount($lolpros) : null;
        }

        return new JsonResponse($game);
    }
}
