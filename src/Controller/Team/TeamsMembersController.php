<?php

namespace App\Controller\Team;

use App\Controller\APIController;
use App\Entity\Team\Team;
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
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getTeamMembersAction(Team $team): Response
    {
        return $this->serialize($team->getMembers(), 'get_team_members');
    }
}
