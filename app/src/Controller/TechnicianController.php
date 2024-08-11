<?php

namespace App\Controller;

use App\Entity\Checklist;
use App\Entity\User;
use App\Service\VisiteFormService;
use App\Repository\VisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/technician')]
class TechnicianController extends AbstractController
{

    private $visiteFormService;


	public function __construct(VisiteFormService $visiteFormService)
    {
        $this->visiteFormService = $visiteFormService;
    }
	
    #[Route('/{visiteId?}', name: 'technician_dashboard', requirements: ['visiteId' => '\d+'])]
    public function index(?int $visiteId, Request $request, VisiteRepository $visiteRepository): Response
    {

		if($visiteId){
			$selectedVisite = $visiteRepository->find($visiteId);
			if (!$selectedVisite){
                $this->addFlash('error', "Visite inexistante.");
                $selectedVisite = null;
                $tasks = null;
			}
			elseif (!$this->isGranted('view_visite', $selectedVisite)) {
                $this->addFlash('error', "Vous n'avez pas access à cette visite.");
                $selectedVisite = null;
                $tasks = null;
            } else {
                $tasks = $selectedVisite->getChecklist()->getTasks();
            }
		} else {
			$selectedVisite=NULL;
			$tasks=NULL;
		}


		// Récupérer l'utilisateur connecté s'il existe
		$user = $this->getUser();

		// Vérifier si l'utilisateur est un admin
		if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
			// Si oui, on affiche toutes les visites
			$form = $this->visiteFormService->createSelectVisiteForm($visite = $selectedVisite);
		} else {
            // Sinon, l'utilisateur est un tehnicien et on n'affiche que les visites qui lui sont affectées
			$form = $this->visiteFormService->createSelectVisiteForm($visite = $selectedVisite, $user = $user);
		}


		// Afficher le formulaire dans le template Twig
		return $this->render('technician/index.html.twig', [
			'form' => $form->createView(),
			'tasks' => $tasks, // Passer les tâches au template Twig
		]);
    }
	
	#[Route('/selection', name: 'technician_selection_checklist')]
    public function readChecklist(Request $request): Response
    {	
        $form = $this->visiteFormService->createSelectVisiteForm();
		$form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $visite = $form->get('visite')->getData();

			if($visite){
				$visiteId = $visite->getId();
			} else {
				$visiteId = null;
			}
		} else {
			$visiteId = null;
		}	
		return $this->redirectToRoute('technician_dashboard', ['visiteId' => $visiteId]);	
	}
}
