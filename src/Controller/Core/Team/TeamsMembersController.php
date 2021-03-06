<?php

namespace App\Controller\Core\Team;

use App\Controller\APIController;
use App\Entity\Core\Team\Team;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teams")
 */
class TeamsMembersController extends APIController
{
    /**
     * @Get(path="/{uuid}/members")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getTeamMembersAction(string $uuid): Response
    {
        /** @var Team $team */
        $team = $this->find(Team::class, $uuid);
        $members = $team->getMembers();

        return $this->serialize($members, 'get_team_members');
    }
}
