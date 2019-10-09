<?php

namespace App\Controller\Core\Player;

use App\Controller\APIController;
use App\Entity\Core\Player\Staff;
use App\Entity\Core\Region\Region;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Form\Core\Player\StaffForm;
use App\Manager\Core\Player\StaffManager;
use Doctrine\Common\Collections\ArrayCollection;
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
 * @Route("/staffs")
 */
class StaffsController extends APIController
{
    /**
     * @Get(path="")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getStaffsAction(): Response
    {
        return $this->serialize($this->getDoctrine()->getRepository(Staff::class)->findBy([], ['name' => 'asc']), 'get_staffs');
    }

    /**
     * @Get(path="/{uuid}")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getStaffAction(string $uuid): Response
    {
        return $this->serialize($this->find(Staff::class, $uuid), 'get_staff');
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotCreatedException
     */
    public function postStaffsAction(StaffManager $staffManager): Response
    {
        $staff = new Staff();
        $postedData = $this->getPostedData();

        $regions = new ArrayCollection();
        foreach ($postedData['regions'] as $region) {
            $regions->add($this->find(Region::class, $region));
        }
        $staff->setRegions($regions);
        unset($postedData['regions']);

        $form = $this
            ->createForm(StaffForm::class, $staff, StaffForm::buildOptions(Request::METHOD_POST, $postedData))
            ->submit($postedData, false);

        if (!$form->isValid()) {
            return new JsonResponse($this->errorFormatter->reduceForm($form), 422);
        }

        $staff = $staffManager->create($staff);

        return $this->serialize($staff, 'get_staff', 201);
    }

    /**
     * @Put(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotUpdatedException
     */
    public function putStaffsAction(string $uuid, StaffManager $staffManager, ValidatorInterface $validator): Response
    {
        /** @var Staff $staff */
        $staff = $this->find(Staff::class, $uuid);
        $postedData = $this->getPostedData();

        $staffData = $this->deserialize(Staff::class, 'put_staff');
        $regions = new ArrayCollection();
        foreach ($postedData['regions'] as $region) {
            $regions->add($this->find(Region::class, is_array($region) ? $region['uuid'] : $region));
        }
        $staffData->setRegions($regions);

        $violationList = $validator->validate($staffData, null, ['put_team']);
        if ($violationList->count() > 0) {
            return new JsonResponse($this->errorFormatter->reduce($violationList), 422);
        }

        $staff = $staffManager->update($staff, $staffData);

        return $this->serialize($staff, 'get_staff');
    }

    /**
     * @Delete(path="/{uuid}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteStaffsAction(string $uuid, StaffManager $staffManager): Response
    {
        /** @var Staff $staff */
        $staff = $this->find(Staff::class, $uuid);

        $staffManager->delete($staff);

        return new JsonResponse(null, 204);
    }
}
