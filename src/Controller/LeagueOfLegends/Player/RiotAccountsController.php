<?php

namespace App\Controller\LeagueOfLegends\Player;

use App\Controller\APIController;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Exception\LeagueOfLegends\AccountRecentlyUpdatedException;
use App\Form\LeagueOfLegends\Player\RiotAccountForm;
use App\Manager\LeagueOfLegends\Player\RiotAccountManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/riot-accounts")
 */
class RiotAccountsController extends APIController
{
    /**
     * @Get(path="")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @IsGranted("ROLE_ADMIN")
     */
    public function getRiotAccountsAction(ParamFetcher $paramFetcher): Response
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');
        $accounts = $this->getDoctrine()->getRepository(RiotAccount::class)->getPaginated($page, $pageSize);
        $total = $accounts->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $accounts->getIterator()->getArrayCopy(),
        ], 'league.get_riot_accounts');
    }

    /**
     * @Get(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getRiotAccountAction($uuid): Response
    {
        return $this->serialize($this->find(RiotAccount::class, $uuid), 'league.get_riot_account');
    }

    /**
     * @Put(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putRiotAccountAction(string $uuid, RiotAccountManager $riotAccountManager): Response
    {
        /** @var RiotAccount $riotAccount */
        $riotAccount = $this->find(RiotAccount::class, $uuid);
        $postedData = $this->getPostedData();

        $form = $this
            ->createForm(RiotAccountForm::class, $riotAccount, RiotAccountForm::buildOptions(Request::METHOD_PUT, $postedData))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $riotAccount = $riotAccountManager->update($riotAccount);

        return $this->serialize($riotAccount, 'league.get_riot_account');
    }

    /**
     * @Put(path="/{uuid}/update")
     * @IsGranted("ROLE_ADMIN")
     */
    public function putRiotAccountRefreshAction(string $uuid, RiotAccountManager $riotAccountManager): Response
    {
        /** @var RiotAccount $riotAccount */
        $riotAccount = $this->find(RiotAccount::class, $uuid);
        try {
            $riotAccount = $riotAccountManager->refreshRiotAccount($riotAccount);
        } catch (AccountRecentlyUpdatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($riotAccount, 'league.get_riot_account');
    }

    /**
     * @Delete(path="/{uuid}")
     */
    public function deleteRiotAccountAction(string $uuid, RiotAccountManager $riotAccountManager): Response
    {
        /** @var RiotAccount $riotAccount */
        $riotAccount = $this->find(RiotAccount::class, $uuid);
        try {
            $riotAccountManager->delete($riotAccount);
        } catch (EntityNotDeletedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return new JsonResponse(null, 204);
    }
}
