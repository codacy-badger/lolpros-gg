<?php

namespace App\Controller\Report;

use App\Controller\APIController;
use App\Entity\Report\AdminLog;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin-logs")
 */
class AdminLogController extends APIController
{
    /**
     * @Get(path="")
     * @QueryParam(name="page", default=1, nullable=true)
     * @QueryParam(name="per_page", default=20, nullable=true)
     * @QueryParam(name="user", nullable=true)
     * @QueryParam(name="type", nullable=true)
     * @IsGranted("ROLE_ADMIN")
     */
    public function getAdminLogsAction(ParamFetcher $paramFetcher)
    {
        $page = (int) $paramFetcher->get('page');
        $pageSize = (int) $paramFetcher->get('per_page');
        $logs = $this->getDoctrine()->getRepository(AdminLog::class)->getPaginated(
            $page,
            $pageSize,
            $paramFetcher->get('type'),
            $paramFetcher->get('user')
        );
        $total = $logs->count();

        return $this->serialize([
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'current' => $page,
            'per_page' => $pageSize,
            'results' => $logs->getIterator()->getArrayCopy(),
        ], 'get_admin_logs');
    }
}
