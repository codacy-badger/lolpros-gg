<?php

namespace App\Controller\Core\Identity;

use App\Controller\APIController;
use App\Entity\Core\Identity\Identity;
use App\Entity\Core\Identity\SocialMedia;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\Core\Identity\SocialMediaManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/players")
 */
class IdentitiesSocialMediaController extends APIController
{
    /**
     * @Get(path="/{uuid}/social-medias")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getIdentitySocialMediasAction(string $uuid): Response
    {
        /** @var Identity $identity */
        $identity = $this->find(Identity::class, $uuid);

        return $this->serialize($identity->getSocialMedia(), 'get_identity_social_medias');
    }

    /**
     * @Put(path="/{uuid}/social-medias")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putIdentitySocialMediasAction(string $uuid, ValidatorInterface $validator, SocialMediaManager $socialMediaManager): Response
    {
        /** @var Identity $identity */
        $identity = $this->find(Identity::class, $uuid);
        $socialMedia = $this->deserialize(SocialMedia::class, 'put_player_social_medias');

        $violationList = $validator->validate($socialMedia, null, ['put_player_social_medias']);
        if ($violationList->count() > 0) {
            return new JsonResponse($this->errorFormatter->reduce($violationList), 422);
        }

        $socialMedia = $socialMediaManager->updateSocialMedia($identity, $socialMedia);

        return $this->serialize($socialMedia, 'get_identity_social_medias');
    }
}
