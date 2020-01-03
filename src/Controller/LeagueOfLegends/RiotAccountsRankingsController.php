<?php

namespace App\Controller\LeagueOfLegends;

use App\Controller\APIController;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Manager\LeagueOfLegends\RankingManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @QueryParam(name="season", nullable=true)
     */
    public function getRiotAccountsRankingsAction(string $uuid, Request $request, RankingManager $rankingManager): Response
    {
        /* @var RiotAccount $riotAccount */
        $riotAccount = $this->find(RiotAccount::class, $uuid);
        $rankings = $rankingManager->getRankingsForRiotAccount($riotAccount, $request->get('months'), $request->get('season'));

        return $this->serialize($rankings, 'league.get_riot_account_rankings');
    }
}
