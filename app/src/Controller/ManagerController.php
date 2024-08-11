<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManagerController extends AbstractController
{	

    #[Route('/manager', name: 'manager_dashboard')]
    public function index(): Response
    {	
		// Afficher le formulaire dans le template Twig
		return $this->render('manager/index.html.twig');
    }
	
}
