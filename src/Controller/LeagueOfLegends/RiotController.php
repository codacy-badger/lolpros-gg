<?php

namespace App\Controller\LeagueOfLegends;

use App\Controller\APIController;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Exception\LeagueOfLegends\AccountAlreadyExistsException;
use App\Manager\LeagueOfLegends\Riot\RiotLeagueManager;
use App\Manager\LeagueOfLegends\Riot\RiotSummonerManager;
use App\Manager\LeagueOfLegends\RiotAccountManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/riot")
 */
class RiotController extends APIController
{
    /**
     * @Get("/summoner/{name}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getRiotPlayerSearchAction(string $name, RiotSummonerManager $riotSummonerManager, RiotAccountManager $riotAccountManager, RiotLeagueManager $riotLeagueManager): Response
    {
        try {
            $summoner = $riotSummonerManager->findPlayer($name);

            if ($riotAccountManager->accountExists($summoner->id)) {
                throw new AccountAlreadyExistsException();
            }

            $summoner->leagues = $riotLeagueManager->getForId($summoner->id);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return new JsonResponse($summoner);
    }

    /**
     * @Get("/challengers")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getMissingChallengersAction(EntityManagerInterface $entityManager, RiotLeagueManager $riotLeagueManager): Response
    {
        try {
            $missing = [];
            $riotAccountRepository = $entityManager->getRepository(RiotAccount::class);

            $challengers = $riotLeagueManager->getChallengers()->entries;
            foreach ($challengers as $challenger) {
                if (($account = $riotAccountRepository->findOneBy(['encryptedRiotId' => $challenger->summonerId]))) {
                    continue;
                }
                $missing[] = [
                    'summoner_name' => $challenger->summonerName,
                    'summoner_id' => $challenger->summonerId,
                    'opgg' => 'https://euw.op.gg/summoner/userName='.$challenger->summonerName,
                ];
            }
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return new JsonResponse($missing);
    }
}
