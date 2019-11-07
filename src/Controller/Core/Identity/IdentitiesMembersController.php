<?php

namespace App\Controller\Core\Identity;

use App\Controller\APIController;
use App\Entity\Core\Identity\Identity;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/identities")
 */
class IdentitiesMembersController extends APIController
{
    /**
     * @Get(path="/{uuid}/members")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getIdentitiesMembersAction(string $uuid): Response
    {
        /** @var Identity $identity */
        $identity = $this->find(Identity::class, $uuid);

        return $this->serialize($identity->getMemberships(), 'get_identity_memberships');
    }
}
