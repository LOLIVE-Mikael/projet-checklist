<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChecklistRepository;
use App\Repository\TaskRepository;
use App\Service\ApiErrorHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;




class ApiPatchTaskController extends AbstractController
{
	private $entityManager;
    private $taskRepository;
    private $validator;
    private $errorHandler;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, TaskRepository $taskRepository, ApiErrorHandler $errorHandler)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->validator = $validator;
        $this->errorHandler = $errorHandler;
    }


    #[Route('/api/tasks/{id}', name: 'api_task_update', methods: ['PATCH'])]
    public function __invoke(int $id, Request $request): Response
    {
        // Récupérer la tâche à mettre à jour
        $task = $this->taskRepository->find($id);
        if (!$task) {
            return $this->errorHandler->createCustomErrorResponse(
                JsonResponse::HTTP_NOT_FOUND,
                'Tâche non trouvée',
                'Not Found',
                '/errors/404'
            );
        }

        // Décoder les données de la requête JSON
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les champs
        try {
            if (isset($data['title'])) {
                $task->setTitle($data['title']);
            }
            if (isset($data['duree'])) {
                $task->setDurationFromString($data['duree']);
            }
        } catch (\Exception $e) {
            return $this->errorHandler->createErrorResponse($e, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validation de la tâche après la mise à jour
        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            return $this->errorHandler->createValidationErrorResponse($errors);
        }

        // Sauvegarder les changements dans la base de données
        try {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->errorHandler->createErrorResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'success', 'data' => $task], JsonResponse::HTTP_OK);
    }
}
