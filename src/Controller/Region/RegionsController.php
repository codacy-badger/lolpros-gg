<?php

namespace App\Controller\Region;

use App\Controller\APIController;
use App\Entity\Region\Region;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
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
     * @Get(path="/{uuid}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getRegionAction(string $uuid): Response
    {
        return $this->serialize($this->find(Region::class, $uuid), 'get_region');
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
     * @Put(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putRegionAction(string $uuid, RegionManager $regionManager): Response
    {
        /** @var Region $region */
        $region = $this->find(Region::class, $uuid);
        $postedData = $this->getPostedData();

        $form = $this
            ->createForm(RegionForm::class, $region, RegionForm::buildOptions(Request::METHOD_PUT))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        /* @var Region $region*/
        $region->setCountries($postedData['countries']);

        $region = $regionManager->update($region);

        return $this->serialize($region, 'get_region');
    }

    /**
     * @Delete(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteRegionsAction(string $uuid, RegionManager $regionManager): Response
    {
        /** @var Region $region */
        $region = $this->find(Region::class, $uuid);

        $regionManager->delete($region);

        return new JsonResponse(null, 204);
    }
}
