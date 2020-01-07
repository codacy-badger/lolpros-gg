<?php

namespace App\Controller\Report;

use App\Controller\APIController;
use App\Entity\Report\AddRequest;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Form\Core\Report\AddRequestForm;
use App\Manager\Report\AddRequestManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/add-requests")
 */
class AddRequestController extends APIController
{
    /**
     * @Get(path="")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getAddRequestsAction(): Response
    {
        $requests = $this->getDoctrine()->getRepository(AddRequest::class)->findAll();

        return $this->serialize($requests, 'get_add_requests');
    }

    /**
     * @Get(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getAddRequestAction(string $uuid): Response
    {
        $request = $this->find(AddRequest::class, $uuid);

        return $this->serialize($request, 'get_add_request');
    }

    /**
     * @Post(path="")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @throws EntityNotCreatedException
     */
    public function postAddRequestsAction(AddRequestManager $addRequestManager): Response
    {
        $request = new AddRequest();
        $postedData = $this->getPostedData();

        $form = $this
            ->createForm(AddRequestForm::class, $request, AddRequestForm::buildOptions(Request::METHOD_POST))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $request = $addRequestManager->create($request);

        return $this->serialize($request, 'get_add_request', 201);
    }

    /**
     * @Put(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putAddRequestAction(string $uuid, AddRequestManager $addRequestManager): Response
    {
        /** @var AddRequest $request */
        $request = $this->find(AddRequest::class, $uuid);
        $postedData = $this->getPostedData();

        $form = $this
            ->createForm(AddRequestForm::class, $request, AddRequestForm::buildOptions(Request::METHOD_PUT))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $request = $addRequestManager->update($request);

        return $this->serialize($request, 'get_add_request');
    }

    /**
     * @Delete(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteAddRequestsAction(string $uuid, AddRequestManager $addRequestManager): Response
    {
        /** @var AddRequest $request */
        $request = $this->find(AddRequest::class, $uuid);

        $addRequestManager->delete($request);

        return new JsonResponse(null, 204);
    }
}
