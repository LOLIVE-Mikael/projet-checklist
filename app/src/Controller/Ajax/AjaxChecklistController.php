<?php
 
namespace App\Controller\Ajax;

use App\Entity\Checklist;
use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ChecklistRepository;
use App\Repository\TaskRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TaskFormService;
use App\Service\ApiErrorHandler;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/ajax/checklist')]
class AjaxChecklistController extends AbstractController
{
    private $taskFormService;
	private $checklistRepository;
	private $taskRepository;
	private $entityManager;
	private $validator;
	private $errorHandler;

    public function __construct(
		TaskFormService $taskFormService,
        ChecklistRepository $checklistRepository,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager,
		ValidatorInterface $validator,
		ApiErrorHandler $errorHandler
    ) {
        $this->taskFormService = $taskFormService;
        $this->checklistRepository = $checklistRepository;
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
		$this->validator = $validator;
		$this->errorHandler = $errorHandler;
    }
	
    #[Route('/', name: 'checklist_ajax_data')]
    public function getChecklistData(Request $request): Response
    {
		// Récupérer l'ID de la checklist depuis la requête AJAX
		$checklistId = $request->query->get('checklistId');
 
		// Initialiser les variables par défaut
		$selectedChecklist = null;
		$tasks = [];
		$formAdd = null;

		if($checklistId){

			$selectedChecklist = $this->checklistRepository->find($checklistId);

			if ($selectedChecklist) {
				$tasks = $selectedChecklist->getTasks();
				$formAdd = $this->taskFormService->createAddTaskForChecklistForm($selectedChecklist)->createView();
			} else {
				return $this->errorHandler->createCustomErrorResponse(
					JsonResponse::HTTP_NOT_FOUND,
					'Checklist non trouvée',
					'Not Found',
					'/errors/404'
				);
			}
		} 
		// Afficher le formulaire dans le template Twig
		return $this->render('checklist/tasks.html.twig', [
			'formadd' => $formAdd,
			'tasks' => $tasks, 
			'checklist' => $selectedChecklist,
		]);			
	}

    #[Route('/duration', name: 'checklist_ajax_duration')]
    public function getDurationData(ChecklistRepository $checklistRepository, Request $request): Response
    {
		// Récupérer l'ID de la checklist depuis la requête AJAX
		$checklistId = $request->query->get('checklistId');
		if(!$checklistId){
			return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_NOT_FOUND,
				'Checklist non trouvée',
				'Not Found',
				'/errors/404'
			);
		}	

		// Récupérer la checklist correspondant à l'ID
		$selectedChecklist =  $this->checklistRepository->find($checklistId);
		
		// Vérifier si la checklist est trouvée
		if (!$selectedChecklist) {
			return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_NOT_FOUND,
				'Checklist non trouvée',
				'Not Found',
				'/errors/404'
			);
		}		
		
		return $this->render('checklist/duration.html.twig', [ 
			'checklist' => $selectedChecklist,
		]);
	}
	
    #[Route('/removetask', name: 'checklist_ajax_remove_task')]
    public function removeTaskAjax(Request $request): Response
    {
	    // Récupérer les ID de la tâche et de la checklist depuis la requête AJAX
	    $taskId = $request->request->get('taskId');
	    $checklistId = $request->request->get('checklistId');

	    // Vérifier si les ID de la tâche et de la checklist sont corrects
		if (!$taskId || !$checklistId) {
			return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_NOT_FOUND,
				'ID de Checklist ou de la tâche non trouvée',
				'Not Found',
				'/errors/404'
			);
		}

	    // Récupérer la tâche et la checklist correspondantes	
		$task = $this->taskRepository->find($taskId);
		$checklist = $this->checklistRepository->find($checklistId);
		
	    // Vérifier si la tâche et la checklist existent	
		if (!$task || !$checklist) {
			return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_NOT_FOUND,
				'Checklist ou tâche non trouvée',
				'Not Found',
				'/errors/404'
			);
		}

		// Dissocier la tâche de la checklist

		try {
			$checklist->removeTask($task);
			$this->entityManager->flush();
		} catch (\Exception $e) {
			return $this->errorHandler->createErrorResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
		}
		
	    // Retourner une réponse JSON indiquant le succès de l'opération
		return new JsonResponse(['success' => true]);		
	}
	
	#[Route('/addtask', name: 'checklist_ajax_add_task')]
    public function addTaskAjax(Request $request): Response
	{
    	// Récupérer les données de la requête
		$checklistId = $request->request->get('checklistId');
		$taskId = $request->request->get('taskId');

		// Vérifier si l'ID de la checklist est fourni
		if (!$checklistId) {
			return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_NOT_FOUND,
				'ID de la checklist non fourni',
				'Not Found',
				'/errors/404'
			);
		}

		// Récupérer ou créer une tâche
		if ($taskId) {
			$task = $this->taskRepository->find($taskId);
			if (!$task) {
				return new JsonResponse(['error' => 'Tâche non trouvée'], 404);
			}
		} else { 
			$task = new Task();
			try {
				$task->setTitle($request->request->get('title'));
				$task->setDurationFromString($request->request->get('duration'));
			} catch (\Exception $e) {
				return $this->errorHandler->createErrorResponse($e, Response::HTTP_UNPROCESSABLE_ENTITY);
			}
			// Valider les données de la nouvelle tâche
			$errors = $this->validator->validate($task);
			if (count($errors) > 0) {
				$errorsArray = [];
				foreach ($errors as $error) {
					$errorsArray[] = ['property' => $error->getPropertyPath(), 'message' => $error->getMessage()];
				}
				return new JsonResponse(['violations' => $errorsArray], 422);
			}
			try {
				// Persistez et flush la nouvelle tâche
				$this->entityManager->persist($task);
				$this->entityManager->flush();
			} catch (\Exception $e) {
				return $this->errorHandler->createErrorResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		}

        $checklist = $this->checklistRepository->find($checklistId);
		$checklist->getTasks();	

		$task->getChecklists()->initialize();		
        $checklist->addTask($task);

		try {
			$this->entityManager->persist($checklist);
			$this->entityManager->flush();
		} catch (\Exception $e) {
			return $this->errorHandler->createErrorResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		// Afficher la formulaire dans le template Twig
		return $this->render('checklist/task.html.twig', [
			'task' => $task,
			'checklist' => $checklist
		]);	
		
	}
}
