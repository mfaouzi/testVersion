<?php

namespace Aliznet\EnrichBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AliznetEnrichBundle:Default:index.html.twig', array('name' => $name));
    }
}
