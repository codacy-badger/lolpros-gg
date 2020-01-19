<?php

namespace App\Controller\Team;

use App\Controller\APIController;
use App\Entity\Team\Member;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Manager\Team\MemberManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/members")
 */
class MembersController extends APIController
{
    /**
     * @Get(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getMemberAction(Member $member): Response
    {
        return $this->serialize($member, 'get_member');
    }

    /**
     * @Post(path="")
     * @IsGranted("ROLE_ADMIN")
     */
    public function postMembersAction(MemberManager $memberManager): Response
    {
        try {
            $member = $memberManager->create($this->getPostedData());
        } catch (EntityNotCreatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($member, 'get_member', 201);
    }

    /**
     * @Put(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function putMembersAction(Member $member, MemberManager $memberManager): Response
    {
        try {
            $member = $memberManager->update($member, $this->deserialize(Member::class, 'put_member'));
        } catch (EntityNotUpdatedException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        return $this->serialize($member, 'get_member');
    }

    /**
     * @Delete(path="/{uuid}", requirements={"uuid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws EntityNotDeletedException
     */
    public function deleteMembersAction(string $uuid, MemberManager $memberManager): Response
    {
        /** @var Member $member */
        $member = $this->find(Member::class, $uuid);

        $memberManager->delete($member);

        return new JsonResponse(null, 204);
    }
}
