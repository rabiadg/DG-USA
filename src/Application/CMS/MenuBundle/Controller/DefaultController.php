<?php

namespace App\Application\CMS\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function indexAction($name)
    {
        return $this->render('/Application/CMS/MenuBundle/Resources/views/Default/index.html.twig', array('name' => $name));
    }
}
