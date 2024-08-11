<?php

namespace App\Controller\Ajax;

use App\Entity\Task;
use App\Form\TaskType;
use App\Service\TaskFormService;
use App\Service\ApiErrorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateInterval;
 
#[Route('/ajax/task')]
class AjaxTaskController extends AbstractController
{

	private $validator;
    private $taskFormService;
	private $errorHandler;

    public function __construct(
		ValidatorInterface $validator,
		TaskFormService $taskFormService,
		ApiErrorHandler $errorHandler
    ) {
		$this->validator = $validator;
		$this->taskFormService = $taskFormService;
		$this->errorHandler = $errorHandler;
    }

	
    #[Route('/add', name: 'task_ajax_add')]
    public function addTask(EntityManagerInterface $entityManager, Request $request): Response
    {
		//création de la nouvelle tâche
		$data = json_decode($request->getContent(), true);
		$title = $data['title'];
		$duration = $data['duration'];
	
		$task = new Task();
		try {
			$task->setTitle($title);
			$task->setDurationFromString($duration);
		} catch (\Exception $e) {
			return $this->errorHandler->createErrorResponse($e, Response::HTTP_UNPROCESSABLE_ENTITY);
		}
		$errors = $this->validator->validate($task);
		if (count($errors) > 0) {
			return $this->errorHandler->createValidationErrorResponse($errors);
		} else {
			try {
				$entityManager->persist($task);
				$entityManager->flush();
			} catch (\Exception $e) {
				return $this->errorHandler->createErrorResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		}

		$form = $this->taskFormService->createEditTaskForm($task)
			->createView();
		
		return $this->render('task/formtask.html.twig', [
            'form' => $form,
			'taskId' => $task->getId(),
        ]);
	}
}
