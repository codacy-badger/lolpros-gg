<?php

namespace App\Controller\Profile;

use App\Controller\APIController;
use App\Entity\Profile\Profile;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Exception\LeagueOfLegends\AccountRecentlyUpdatedException;
use App\Manager\LeagueOfLegends\RiotAccountManager;
use App\Manager\Profile\ProfileManager;
use App\Repository\ProfileRepository;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function getProfileAction(Profile $profile): Response
    {
        return $this->serialize($profile, 'get_profile');
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     */
    public function postProfilesAction(ProfileManager $profileManager): Response
    {
        try {
            $profile = $profileManager->create($this->getPostedData());
        } catch (EntityNotCreatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($profile, 'get_profile', 201);
    }

    /**
     * @Put(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function putProfilesAction(Profile $profile, ProfileManager $profileManager): Response
    {
        try {
            $profile = $profileManager->update($profile, $this->getPostedData());
        } catch (EntityNotUpdatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($profile, 'get_profile');
    }

    /**
     * @Delete(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteProfilesAction(Profile $profile, ProfileManager $profileManager): Response
    {
        $profileManager->delete($profile);

        return new JsonResponse(null, 204);
    }

    /**
     * @Get(path="/{uuid}/update")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @throws ServerLimitException
     */
    public function getProfileUpdateAction(Profile $profile, RiotAccountManager $riotAccountManager): Response
    {
        $errorCount = 0;
        $accounts = $profile->getLeaguePlayer()->getAccounts();

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
