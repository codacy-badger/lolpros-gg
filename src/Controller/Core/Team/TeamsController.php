<?php

namespace App\Controller\Core\Team;

use App\Controller\APIController;
use App\Entity\Core\Region\Region;
use App\Entity\Core\Team\Team;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Form\Core\Team\TeamForm;
use App\Manager\Core\Team\TeamManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/teams")
 */
class TeamsController extends APIController
{
    /**
     * @Get(path="")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getTeamsAction(): Response
    {
        return $this->serialize($this->getDoctrine()->getRepository(Team::class)->findBy([], ['name' => 'asc']), 'get_teams');
    }

    /**
     * @Get(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getTeamAction(string $uuid): Response
    {
        return $this->serialize($this->find(Team::class, $uuid), 'get_team');
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotCreatedException
     */
    public function postTeamsAction(TeamManager $teamManager): Response
    {
        $team = new Team();
        $postedData = $this->getPostedData();

        $team->setRegion($this->find(Region::class, $postedData['region']['uuid']));
        unset($postedData['region']);

        $form = $this
            ->createForm(TeamForm::class, $team, TeamForm::buildOptions(Request::METHOD_POST, $postedData))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $team = $teamManager->create($team);

        return $this->serialize($team, 'get_team', 201);
    }

    /**
     * @Put(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putTeamsAction(string $uuid, Request $request, TeamManager $teamManager, ValidatorInterface $validator): Response
    {
        $content = json_decode($request->getContent());
        /** @var Team $team */
        $team = $this->find(Team::class, $uuid);
        $teamData = $this->deserialize(Team::class, 'put_team');
        $region = $this->find(Region::class, $content->region->uuid);
        $teamData->setRegion($region);

        $violationList = $validator->validate($teamData, null, ['put_team']);
        if ($violationList->count() > 0) {
            return new JsonResponse($this->errorFormatter->reduce($violationList), 422);
        }

        $team = $teamManager->update($team, $teamData);

        return $this->serialize($team, 'get_team');
    }

    /**
     * @Delete(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteTeamsAction(string $uuid, TeamManager $teamManager): Response
    {
        /** @var Team $team */
        $team = $this->find(Team::class, $uuid);

        $teamManager->delete($team);

        return new JsonResponse(null, 204);
    }
}
