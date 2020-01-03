<?php

namespace App\Controller\LeagueOfLegends;

use App\Controller\APIController;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Entity\Profile\Profile;
use App\Manager\LeagueOfLegends\RiotAccountManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/players")
 */
class LeaguePlayersRiotAccountsController extends APIController
{
    /**
     * @Get(path="/{uuid}/riot-accounts")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getLeaguePlayersRiotAccountsAction(string $uuid): Response
    {
        /* @var Profile $player */
        $player = $this->find(Profile::class, $uuid);

        if (!($leaguePlayer = $player->getLeaguePlayer())) {
            return new JsonResponse([]);
        }

        return $this->serialize($leaguePlayer->getAccounts(), 'league.get_player_riot_accounts');
    }

    /**
     * @Post(path="/{uuid}/riot-accounts")
     * @IsGranted("ROLE_ADMIN")
     */
    public function postLeaguePlayerRiotAccountAction(string $uuid, RiotAccountManager $riotAccountManager): Response
    {
        /* @var Profile $player */
        $player = $this->find(Profile::class, $uuid);
        $riotAccount = $this->deserialize(RiotAccount::class, 'league.post_riot_account');
        $riotAccount = $riotAccountManager->createRiotAccount($riotAccount, $player);

        return $this->serialize($riotAccount, 'league.get_riot_account', 201);
    }
}
