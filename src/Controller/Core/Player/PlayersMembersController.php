<?php

namespace App\Controller\Core\Player;

use App\Controller\APIController;
use App\Entity\Core\Player\Player;
use App\Entity\Core\Team\Member;
use App\Factory\Core\TeamFactory;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/players")
 */
class PlayersMembersController extends APIController
{
    /**
     * @Get(path="/{uuid}/members")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getPlayersMembersAction(string $uuid): Response
    {
        /** @var Player $player */
        $player = $this->find(Player::class, $uuid);

        return $this->serialize($player->getMemberships(), 'get_player_members');
    }

    /**
     * @Get(path="/{uuid}/members/previous")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getPlayersMembersPreviousAction(string $uuid): Response
    {
        /** @var Player $player */
        $player = $this->find(Player::class, $uuid);

        $current = $player->getCurrentTeam();
        $memberships = [];
        foreach ($player->getMemberships() as $membership) {
            /** @var Member $membership */
            if ($membership->isCurrent() || ($current && $current->getUuid() === $membership->getTeam()->getUuid())) {
                continue;
            }

            $memberships[] = TeamFactory::createFromMembership($membership);
        }

        return $this->serialize($memberships, 'get_player_memberships');
    }
}
