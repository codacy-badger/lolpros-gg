<?php

namespace App\Controller\Region;

use App\Controller\APIController;
use App\Entity\Region\Region;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Form\Core\Region\RegionForm;
use App\Manager\Region\RegionManager;
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
 * @Route("/regions")
 */
class RegionsController extends APIController
{
    /**
     * @Get(path="")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getRegionsAction(): Response
    {
        return $this->serialize($this->getDoctrine()->getRepository(Region::class)->findBy([], ['name' => 'asc']), 'get_regions');
    }

    /**
     * @Get(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getRegionAction(Region $region): Response
    {
        return $this->serialize($region, 'get_region');
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotCreatedException
     */
    public function postRegionsAction(RegionManager $regionManager): Response
    {
        $region = new Region();
        $postedData = $this->getPostedData();

        $form = $this
            ->createForm(RegionForm::class, $region, RegionForm::buildOptions(Request::METHOD_POST))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $region = $regionManager->create($region);

        return $this->serialize($region, 'get_region', 201);
    }

    /**
     * @Put(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putRegionAction(Region $region, RegionManager $regionManager): Response
    {
        $postedData = $this->getPostedData();

        $form = $this
            ->createForm(RegionForm::class, $region, RegionForm::buildOptions(Request::METHOD_PUT))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $region->setCountries($postedData['countries']);
        $region = $regionManager->update($region);

        return $this->serialize($region, 'get_region');
    }

    /**
     * @Delete(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteRegionsAction(Region $region, RegionManager $regionManager): Response
    {
        $regionManager->delete($region);

        return new JsonResponse(null, 204);
    }
}
