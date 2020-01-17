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
     *
     * @throws EntityNotUpdatedException
     */
    public function putProfileSocialMediasAction(string $uuid, ValidatorInterface $validator, SocialMediaManager $socialMediaManager): Response
    {
        /** @var Profile $profile */
        $profile = $this->find(Profile::class, $uuid);
        $socialMedia = $this->deserialize(SocialMedia::class, 'put_profile_social_medias');

        $violationList = $validator->validate($socialMedia, null, ['put_profile_social_medias']);
        if ($violationList->count() > 0) {
            return new JsonResponse($this->errorFormatter->reduce($violationList), 422);
        }

        $socialMedia = $socialMediaManager->updateSocialMedia($profile, $socialMedia);

        return $this->serialize($socialMedia, 'get_profile_social_medias');
    }
}
