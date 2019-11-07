<?php

namespace App\Controller\Core\Report;

use App\Controller\APIController;
use App\Entity\Core\Report\AdminLog;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin-logs")
 */
class AdminLogController extends APIController
{
    /**
     * @Get(path="")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getAdminLogsAction()
    {
        $requests = $this->getDoctrine()->getRepository(AdminLog::class)->findBy([], ['createdAt' => 'desc'], 100);

        return $this->serialize($requests, 'get_admin_logs');
    }
}
