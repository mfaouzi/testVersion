<?php

namespace Aliznet\WCSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AliznetWCSBundle:Default:index.html.twig', array('name' => $name));
    }
}
