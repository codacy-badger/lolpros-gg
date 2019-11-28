<?php

namespace App\Controller\Core\Medal;

use App\Controller\APIController;
use App\Entity\Core\Medal\AMedal;
use App\Exception\Core\EntityNotCreatedException;
use App\Manager\Core\Medal\MedalManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/medals")
 */
class MedalsController extends APIController
{
    /**
     * @Get(path="")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getMedalsAction(): Response
    {
        return $this->serialize($this->getDoctrine()->getRepository(AMedal::class)->findAll());
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotCreatedException
     */
    public function postMedalAction(MedalManager $medalsManager): Response
    {
        $medal = $this->deserialize(AMedal::class, 'post_medal');
        $medal = $medalsManager->create($medal);

        return $this->serialize($medal, 'get_medal');
    }
}
