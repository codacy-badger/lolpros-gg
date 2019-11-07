<?php

namespace App\Controller\Core\Identity;

use App\Controller\APIController;
use App\Entity\Core\Identity\Identity;
use App\Repository\Core\IdentityRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/identities")
 */
class IdentitiesController extends APIController
{
    /**
     * @Get(path="/countries")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getIdentitiesCountriesAction(): Response
    {
        /** @var IdentityRepository $identityRepository */
        $identityRepository = $this->getDoctrine()->getRepository(Identity::class);

        return new JsonResponse($identityRepository->getCountries(), 200);
    }
}
