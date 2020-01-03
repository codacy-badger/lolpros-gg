<?php

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Get(path="/")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function getDefaultAction(): Response
    {
        return $this->render('base.html.twig');
    }
}
