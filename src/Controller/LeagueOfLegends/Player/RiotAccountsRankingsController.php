<?php

namespace App\Controller\LeagueOfLegends\Player;

use App\Controller\APIController;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Manager\LeagueOfLegends\Player\RankingManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/riot-accounts")
 */
class RiotAccountsRankingsController extends APIController
{
    /**
     * @Get(path="/{uuid}/rankings")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @QueryParam(name="months", nullable=true)
     */
    public function getRiotAccountsRankingsAction($uuid, Request $request)
    {
        /* @var RiotAccount $riotAccount */
        $riotAccount = $this->find(RiotAccount::class, $uuid);
        $rankings = $this->get(RankingManager::class)->getRankingsForRiotAccount($riotAccount, $request->get('months', 1));

        return $this->serialize($rankings, 'league.get_riot_account_rankings');
    }
}
