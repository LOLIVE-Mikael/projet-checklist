<?php

namespace App\Controller;

use App\Service\VisiteFormService;
use App\Repository\VisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Visite;

use App\Form\VisiteType;


#[Route('/visite')]
class VisiteController extends AbstractController
{
    private $entityManager;
    private $visiteFormService;

    public function __construct(
        EntityManagerInterface $entityManager,
        VisiteFormService $visiteFormService
    ) {
        $this->entityManager = $entityManager;
        $this->visiteFormService = $visiteFormService;
    }

    #[Route('/', name: 'visite_dashboard')]
    public function index(VisiteRepository $visiteRepository): Response
    {
        // Récupérer toutes les visites depuis le repository
        $visites = $visiteRepository->findFutureVisits();

        // Créer un tableau pour stocker les formulaires d'édition et de suppression
        $editForms = [];
        foreach ($visites as $visite) {
            // Créer un formulaire pour chaque visite
            $editForms[$visite->getId()] = $this->visiteFormService->createEditVisiteForm($visite)->createView();
        }
		
        // Créer un formulaire pour ajouter une nouvelle visite
        $newVisiteForm = $this->visiteFormService->createNewVisiteForm()->createView();

        // Passer les formulaires et les visites au template
        return $this->render('visite/index.html.twig', [
            'editForms' => $editForms,
            'newVisiteForm' => $newVisiteForm,
        ]);
    }

	#[Route('/handle/{visiteId}', name: 'visite_handle')]
    public function update(int $visiteId, Request $request, VisiteRepository $visiteRepository): Response{

		// Récupérer la visite à modifier à partir de l'ID
		$visite = $visiteRepository->find($visiteId);

		if (!$visite) {
			// Gérer le cas où la visite n'est pas trouvée
            $this->addFlash('error', 'visite non trouvée');
            return $this->redirectToRoute('visite_dashboard');
		}

		$form = $this->visiteFormService->createEditVisiteForm($visite);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($form->getClickedButton()->getName() == 'delete'){
                try {
                    $this->entityManager->remove($visite);
                    $this->addFlash('success', 'Visite effacée avec succes.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la visite.');			
                }
			} else {
                try {
                    $this->entityManager->persist($visite);
                    $this->addFlash('success', 'La visite a été mise à jour avec succès.');
                    $this->entityManager->persist($visite);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la modification de la visite.');			
                }
            }
            $this->entityManager->flush();
        }
		return $this->redirectToRoute('visite_dashboard');
    }

	#[Route('/new', name: 'visites_new')]
    public function create(Request $request): Response{		
        $visite = new VIsite();

		$form = $this->visiteFormService->createNewVisiteForm($visite);
       // $form = $this->createForm(VisiteType::class, $visite); 
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
		    $visite = $form->getData();
			$this->entityManager->persist($visite);
			$this->entityManager->flush();
            // Ajouter un message de succès
            $this->addFlash('success', 'Visite créée avec succès.');
		} else {
            // Si le formulaire n'est pas valide, gérer les messages d'erreur
            foreach ($form->getErrors(true) as $error) {
                // Ajouter un message flashbag pour chaque erreur de validation
			    $this->addFlash('error', $error->getMessage());
            }	
        }
		// Rediriger vers une autre page après la création de la tâche 
		return $this->redirectToRoute('visite_dashboard');
	}
}
