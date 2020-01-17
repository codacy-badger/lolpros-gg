<?php

namespace App\Controller\Profile;

use App\Controller\APIController;
use App\Entity\Profile\Profile;
use App\Entity\Region\Region;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Exception\LeagueOfLegends\AccountRecentlyUpdatedException;
use App\Form\LeagueOfLegends\Player\PlayerForm;
use App\Manager\LeagueOfLegends\RiotAccountManager;
use App\Manager\Profile\ProfileManager;
use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use RiotAPI\LeagueAPI\Exceptions\ServerLimitException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/profiles")
 */
class ProfilesController extends APIController
{
    /**
     * @Get(path="")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws Exception
     */
    public function getProfilesAction(ParamFetcher $paramFetcher, ProfileRepository $profileRepository): Response
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');
        $profiles = $profileRepository->getPaginated($page, $pageSize);
        $total = $profiles->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $profiles->getIterator()->getArrayCopy(),
        ], 'get_profiles');
    }

    /**
     * @Get(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getProfileAction(string $uuid): Response
    {
        return $this->serialize($this->find(Profile::class, $uuid), 'get_profile');
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotCreatedException
     */
    public function postProfilesAction(ProfileManager $profileManager): Response
    {
        $profile = new Profile();
        $postedData = $this->getPostedData();

        $regions = new ArrayCollection();
        foreach ($postedData['regions'] as $region) {
            $regions->add($this->find(Region::class, $region));
        }
        $profile->setRegions($regions);
        unset($postedData['regions']);

        $form = $this
            ->createForm(PlayerForm::class, $profile, PlayerForm::buildOptions(Request::METHOD_POST, $postedData))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $profile = $profileManager->create($profile);

        return $this->serialize($profile, 'get_profile', 201);
    }

    /**
     * @Put(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putProfilesAction(string $uuid, ProfileManager $profileManager, ValidatorInterface $validator): Response
    {
        /** @var Profile $profile */
        $profile = $this->find(Profile::class, $uuid);
        $postedData = $this->getPostedData();

        $profileData = $this->deserialize(Profile::class, 'put_profile');
        $regions = new ArrayCollection();
        foreach ($postedData['regions'] as $region) {
            $regions->add($this->find(Region::class, is_array($region) ? $region['uuid'] : $region));
        }
        $profileData->setRegions($regions);

        $violationList = $validator->validate($profileData, null, ['put_profile']);
        if ($violationList->count() > 0) {
            return new JsonResponse($this->errorFormatter->reduce($violationList), 422);
        }

        $profile = $profileManager->update($profile, $profileData);

        return $this->serialize($profile, 'get_profile');
    }

    /**
     * @Delete(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteProfilesAction(string $uuid, ProfileManager $profileManager): Response
    {
        /** @var Profile $profile */
        $profile = $this->find(Profile::class, $uuid);

        $profileManager->delete($profile);

        return new JsonResponse(null, 204);
    }

    /**
     * @Get(path="/{uuid}/update")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @throws ServerLimitException
     */
    public function getProfileUpdateAction(string $uuid, RiotAccountManager $riotAccountManager): Response
    {
        /** @var Profile $profile */
        $profile = $this->find(Profile::class, $uuid);
        $errorCount = 0;
        $accounts = $profile->getAccounts();

        foreach ($accounts as $account) {
            try {
                $riotAccountManager->refreshRiotAccount($account);
            } catch (AccountRecentlyUpdatedException $e) {
                ++$errorCount;
            }
        }

        if ($errorCount && $errorCount === $accounts->count()) {
            return new JsonResponse(null, 409);
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @Get(path="/countries")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getProfilesCountriesAction(ProfileRepository $profileRepository): Response
    {
        return new JsonResponse($profileRepository->getCountries(), 200);
    }

    /**
     * @Get(path="/search")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @QueryParam(name="query", nullable=false)
     * @IsGranted("ROLE_ADMIN")
     */
    public function getSearchProfilesAction(ParamFetcher $paramFetcher, ProfileRepository $profileRepository): Response
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');

        $profiles = $profileRepository->searchPaginated($paramFetcher->get('query'), $page, $pageSize);
        $total = $profiles->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $profiles->getIterator()->getArrayCopy(),
        ], 'get_profiles');
    }
}
