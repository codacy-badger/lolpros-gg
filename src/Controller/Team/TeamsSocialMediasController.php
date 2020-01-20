<?php

namespace App\Controller\Team;

use App\Controller\APIController;
use App\Entity\Team\Team;
use App\Exception\EntityNotUpdatedException;
use App\Manager\Team\SocialMediaManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/teams")
 */
class TeamsSocialMediasController extends APIController
{
    /**
     * @Get(path="/{uuid}/social-medias")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getTeamSocialMediasAction(Team $team): Response
    {
        return $this->serialize($team->getSocialMedia(), 'get_team_social_medias');
    }

    /**
     * @Put(path="/{uuid}/social-medias")
     * @IsGranted("ROLE_ADMIN")
     */
    public function putTeamSocialMediasAction(Team $team, SocialMediaManager $socialMediaManager, ValidatorInterface $validator): Response
    {
        try {
            $socialMedia = $socialMediaManager->updateSocialMedia($team, $this->getPostedData());
        } catch (EntityNotUpdatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($socialMedia, 'get_team_social_medias');
    }
}
