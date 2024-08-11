<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Service\TaskFormService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/task')]
class TaskController extends AbstractController
{
    private $entityManager;
    private $taskRepository;
    private $taskFormService;
    public function __construct(
        EntityManagerInterface $entityManager,
        TaskRepository $taskRepository,
		TaskFormService $taskFormService
    ) {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
		$this->taskFormService = $taskFormService;
    }

    #[Route('/', name: 'task_dashboard')]
    public function index(): Response
    {
		$tasks = $this->taskRepository->findAllNonArchived();
        $forms = [];
        foreach ($tasks as $task) {
			$form = $this->taskFormService->createEditTaskForm($task);
			$forms[$task->getId()] = $form->createView();
        }

		// Créer un formulaire pour créer une nouvelle tâche
		$newTask = new Task();
		$newTaskForm = $this->taskFormService->createNewTaskForm($newTask)
			->createView();

        return $this->render('task/index.html.twig', [
            'forms' => $forms,
			'newTaskForm' => $newTaskForm,			
        ]);
    }
	
	#[Route('/handle/{taskId}', name: 'task_handle')]
    public function update(int $taskId, Request $request): Response{

		// Récupérer la tâche à modifier à partir de l'ID
		$task = $this->taskRepository->find($taskId);

		if (!$task) {
			// Gérer le cas où la tâche n'est pas trouvée
			$this->addFlash('error', 'La tâche demandée n\'existe pas.');
            return $this->redirectToRoute('task_dashboard');
		}
		$form = $this->taskFormService->createEditTaskForm($task);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			if ($form->getClickedButton()->getName() == 'delete'){
                $task->archive(); // Marquer la tâche comme archivée
                $this->entityManager->flush();
                $this->addFlash('success', 'La tâche a été supprimée avec succès.');
			} else {
                // Si ce n'est pas une suppression, c'est une modification
                $this->entityManager->persist($task);
                $this->entityManager->flush();
                $this->addFlash('success', 'La tâche a été mise à jour avec succès.');
            }
		}
        // Si le formulaire n'est pas valide, gérer les messages d'erreur
        foreach ($form->getErrors(true) as $error) {
            // Ajouter un message flashbag pour chaque erreur de validation
			$this->addFlash('error', $error->getMessage());
        }		
		return $this->redirectToRoute('task_dashboard');
	}

	#[Route('/new', name: 'task_new')]
    public function create(Request $request): Response{		
		$task = new Task();
		$form = $this->taskFormService->createNewTaskForm($task);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->entityManager->persist($task);
			$this->entityManager->flush();

			$this->addFlash('success', 'La tâche a été créée avec succès.');
			return $this->redirectToRoute('task_dashboard');
		}
        // Si le formulaire n'est pas valide, gérer les messages d'erreur
        foreach ($form->getErrors(true) as $error) {
            // Ajouter un message flashbag pour chaque erreur de validation
			$this->addFlash('error', $error->getMessage());
        }
		return $this->redirectToRoute('task_dashboard');
	}
}
