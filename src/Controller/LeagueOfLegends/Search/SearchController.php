<?php

namespace App\Controller\LeagueOfLegends\Search;

use App\Controller\APIController;
use App\Entity\Core\Team\Team;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search")
 */
class SearchController extends APIController
{
    /**
     * @Get(path="/players/{name}")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @IsGranted("ROLE_ADMIN")
     */
    public function getSearchPlayersAction(string $name, ParamFetcher $paramFetcher): Response
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');

        $players = $this->getDoctrine()->getRepository(Player::class)->searchPaginated($name, $page, $pageSize);
        $total = $players->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $players->getIterator()->getArrayCopy(),
        ], 'league.get_players');
    }

    /**
     * @Get(path="/riot-accounts/{name}")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @IsGranted("ROLE_ADMIN")
     */
    public function getSearchRiotAccountsAction(string $name, ParamFetcher $paramFetcher): Response
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');

        $players = $this->getDoctrine()->getRepository(RiotAccount::class)->searchPaginated($name, $page, $pageSize);
        $total = $players->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $players->getIterator()->getArrayCopy(),
        ], 'league.get_riot_accounts');
    }

    /**
     * @Get(path="/teams/{name}")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @IsGranted("ROLE_ADMIN")
     */
    public function getSearchTeamsAction(string $name, ParamFetcher $paramFetcher): Response
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');

        $teams = $this->getDoctrine()->getRepository(Team::class)->searchPaginated($name, $page, $pageSize);
        $total = $teams->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $teams->getIterator()->getArrayCopy(),
        ], 'get_teams');
    }
}
