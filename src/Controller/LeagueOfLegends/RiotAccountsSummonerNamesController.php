<?php

namespace App\Controller\LeagueOfLegends;

use App\Controller\APIController;
use App\Entity\LeagueOfLegends\RiotAccount;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/riot-accounts")
 */
class RiotAccountsSummonerNamesController extends APIController
{
    /**
     * @Get(path="/{uuid}/summoner-names")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getRiotAccountsSummonerNamesAction(string $uuid): Response
    {
        /* @var RiotAccount $riotAccount */
        $riotAccount = $this->find(RiotAccount::class, $uuid);

        return $this->serialize($riotAccount->getSummonerNames(), 'league.get_riot_account_summoner_names');
    }
}
