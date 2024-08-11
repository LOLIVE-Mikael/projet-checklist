<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChecklistRepository;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Attribute\Route;

class ApiDeleteTaskController extends AbstractController
{
	
	private $entityManager;
    private $taskRepository;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;		
    }

    #[Route('/api/tasks/{id}', name: 'api_task_archive', methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        try {
            // Appeler la méthode archive directement sur l'entité
            $task->archive();
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            return new JsonResponse(['status' => 'Tache archivée'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
