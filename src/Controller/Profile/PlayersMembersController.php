<?php

namespace App\Controller\Profile;

use App\Controller\APIController;
use App\Entity\Profile\Profile;
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
        /** @var Profile $profile */
        $profile = $this->find(Profile::class, $uuid);

        return $this->serialize($profile->getMemberships(), 'get_profile_memberships');
    }
}
