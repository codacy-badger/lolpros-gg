<?php

namespace App\Controller\Profile;

use App\Controller\APIController;
use App\Entity\Profile\Profile;
use App\Entity\Profile\SocialMedia;
use App\Exception\EntityNotUpdatedException;
use App\Manager\Profile\SocialMediaManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/profiles")
 */
class ProfilesSocialMediaController extends APIController
{
    /**
     * @Get(path="/{uuid}/social-medias")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getProfileSocialMediasAction(string $uuid): Response
    {
        /** @var Profile $profile */
        $profile = $this->find(Profile::class, $uuid);

        return $this->serialize($profile->getSocialMedia(), 'get_profile_social_medias');
    }

    /**
     * @Put(path="/{uuid}/social-medias")
     * @IsGranted("ROLE_ADMIN")
     */
    public function putProfileSocialMediasAction(Profile $profile, SocialMediaManager $socialMediaManager): Response
    {
        try {
            $socialMedia = $socialMediaManager->updateSocialMedia($profile, $this->getPostedData());
        } catch (EntityNotUpdatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($socialMedia, 'get_profile_social_medias');
    }
}
