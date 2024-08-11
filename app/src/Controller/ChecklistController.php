<?php

namespace App\Controller;

use App\Entity\Checklist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ChecklistRepository;
use App\Repository\TaskRepository;
use App\Service\TaskFormService;
use App\Service\ChecklistFormService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/checklist')]
class ChecklistController extends AbstractController
{

    private $taskFormService;
    private $checklistFormService;
    private $entityManager;
    private $validator;
    private $checklistRepository;
    private $taskRepository;

    public function __construct(
        TaskFormService $taskFormService,
        ChecklistFormService $checklistFormService,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ChecklistRepository $checklistRepository,
        TaskRepository $taskRepository
    ) {
        $this->taskFormService = $taskFormService;
        $this->checklistFormService = $checklistFormService;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->checklistRepository = $checklistRepository;
        $this->taskRepository = $taskRepository;
    }
	
    #[Route('/{checklistId?}', name: 'checklist_dashboard', requirements: ['checklistId' => '\d+'])]
    public function index(?int $checklistId, Request $request): Response
    {	
		//recuperation de la checklist si une checklist a été sélectionnée
        $selectedChecklist = $checklistId ? $this->checklistRepository->find($checklistId) : null;

		// Vérification de l'existance de la checklist
		if ($checklistId && !$selectedChecklist) {
			$this->addFlash('error', 'Checklist inexistante.');
			return $this->redirectToRoute('checklist_dashboard');
		}

        // Créer le formulaire de sélection de la checklist et le formulaire création de checklist
        $form = $this->checklistFormService->createSelectionForm($this->checklistRepository,$selectedChecklist);
        $formnew = $this->checklistFormService->createAddForm();

		$templateData = [
            'form' => $form->createView(),
            'formnew' => $formnew->createView(),
            'formadd' => null,
            'tasks' => null,
            'checklist' => null,
        ];

        if ($selectedChecklist) {
            // Récupérer les tâches de la checklist sélectionnée
			$tasks = $selectedChecklist->getTasks();
			$formAdd = $this->taskFormService->createAddTaskForChecklistForm($selectedChecklist);

            $templateData['formadd'] = $formAdd->createView();
            $templateData['tasks'] = $tasks;
            $templateData['checklist'] = $selectedChecklist;
        }
       // return $this->render('test_flash/show.html.twig');

		return $this->render('checklist/index.html.twig', $templateData);
    }

    #[Route('/handle', name: 'checklist_handle')]
    public function readChecklist(Request $request): Response
    {
		$form = $this->checklistFormService->createSelectionForm($this->checklistRepository);
		$form->handleRequest($request);
		$checklist = $form->get('checklist')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
        	// Vérifier l'action effectuée par l'utilisateur
            if ($form->get('delete')->isClicked() && $checklist) {
                return $this->deleteChecklist($checklist, $this->entityManager);
            } elseif ($form->get('select')->isClicked() && $checklist) {
                return $this->redirectToRoute('checklist_dashboard', ['checklistId' => $checklist->getId()]);
            }
        }

        $this->addFlash('error', 'Erreur de soumission du formulaire ou aucune action spécifiée.');
        return $this->redirectToRoute('checklist_dashboard');
	}
	
    private function deleteChecklist(Checklist $checklist): Response
    {
		try {
			$checklist->deleteChecklist($this->entityManager);
			$this->addFlash('success', 'Checklist effacée avec succes.');
		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());		
		}	
        return $this->redirectToRoute('checklist_dashboard', ['checklistId' => null]);
    }

    #[Route('/addchecklist', name: 'checklist_ajout_checklist')]	
	public function handleAddChecklist(Request $request)
	{

		$checklist = new Checklist();
		$form = $this->checklistFormService->createAddForm($checklist);
		$form->handleRequest($request);
		
        $errors = $this->validator->validate($checklist);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirectToRoute('checklist_dashboard');
        }

		if ($form->isSubmitted() && $form->isValid()) {
			try {
				$this->entityManager->persist($checklist);
				$this->entityManager->flush();
				$this->addFlash('success', 'Checklist créée avec succès.');
				return $this->redirectToRoute('checklist_dashboard', ['checklistId' => $checklist->getId()]);
			} catch (\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la création de la checklist.');
			}				
		} else  {
			if ($form->isSubmitted()) {
				$this->addFlash('error', 'Le formulaire est invalide.');
			}
		}
		return $this->redirectToRoute('checklist_dashboard');			
	}



    #[Route('/addtache', name: 'checklist_add_task')]	
	public function handleAddTask(Request $request)
    {
		// Récupérer les données soumises par le formulaire
		$formAdd = $this->taskFormService->createAddTaskForChecklistForm();
		$formAdd->handleRequest($request);
		// récupération de la checklist 
		$checklistId = $formAdd->get('checklist_id')->getData(); 
		$selectedChecklist = $this->checklistRepository->find($checklistId);
		if (!$selectedChecklist) {
			$this->addFlash('error', 'Checklist non trouvée.');
			return $this->redirectToRoute('checklist_dashboard');
		}

		if ($formAdd->isSubmitted() && $formAdd->isValid() ) {
			// récupération des données de la tache soumisent par le formulaire 
			$selectedtask = $formAdd->get('task')->getData();
			if ($selectedtask) {
				// Ajouter la tâche sélectionnée à la checklist
				$selectedtask->getChecklists()->initialize();		
				$selectedChecklist->addTask($selectedtask);
				// Persistez et flush la nouvelle tâche
				$this->entityManager->persist($selectedChecklist);
				$this->entityManager->flush();
				$this->addFlash('success', 'Tâche existante ajoutée à la checklist.');
			} else {
				$newTask = $formAdd->getData();	
				// Valider la nouvelle tâche
				$errors = $this->validator->validate($newTask,  null, ['task_creation']);

				if (count($errors) > 0) {
					foreach ($errors as $error) {
						$this->addFlash('error', $error->getMessage());
					}
				} else {
					// Associer la nouvelle tâche à la checklist
					$selectedChecklist->addTask($newTask);

					// Persistez et flush la nouvelle tâche
					$this->entityManager->persist($newTask);
					$this->entityManager->flush();

					$this->addFlash('success', 'Nouvelle tâche créée et ajoutée à la checklist.');
				}
  	      }
		} else { 
			// En cas de soumission invalide, rediriger vers une page d'erreur ou afficher un message d'erreur
			$this->addFlash('error', 'Formulaire incorrect.');
			$errors = $formAdd->getErrors(true, true);
			foreach ($errors as $error) {
			$this->addFlash('error', $error->getMessage());
			}
		} 
		return $this->redirectToRoute('checklist_dashboard', ['checklistId' => $selectedChecklist->getId()]);  
	}
	
	#[Route('/removetask/{taskId}/{checklistId}', name: 'checklist_remove_task')]
	public function removeTask(int $taskId, int $checklistId): Response
	{
		try {
			$task = $this->taskRepository->find($taskId);
			$checklist = $this->checklistRepository->find($checklistId);

			if (!$task || !$checklist) {
				$this->addFlash('error', 'La tâche ou la checklist n\'a pas été trouvée.');
			}

			// Dissocier la tâche de la checklist
			$checklist->removeTask($task);
			$this->entityManager->flush();
			$this->addFlash('success', 'La tâche a été dissociée de la checklist avec succès.');
		} catch (\Exception $e) {
			$this->addFlash('error', 'Une erreur est survenue lors de la dissociation de la tâche.');
		}
	
		return $this->redirectToRoute('checklist_dashboard', ['checklistId' => $checklist->getId()]);	
	}
	
}
