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

class ApiChecklistTaskController extends AbstractController
{
	
	private $entityManager;
	private $checklistRepository;
    private $taskRepository;

    public function __construct(EntityManagerInterface $entityManager, ChecklistRepository $checklistRepository, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->checklistRepository = $checklistRepository;
        $this->taskRepository = $taskRepository;		
		
    }
	
    #[Route('/checklists/{checklistid}/taches/{tacheid}', methods: ['DELETE'])]
    public function __invoke(int $checklistid, int $tacheid): Response
    {
		// Récupérer la checklist
        $checklist = $this->checklistRepository->find($checklistid);

        // Vérifier si la checklist existe
        if (!$checklist) {
            return new JsonResponse(['message' => 'checklist inexistante'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer la tâche
        $task = $this->taskRepository->find($tacheid);

        // Vérifier si la tâche existe
        if (!$task) {
            return new JsonResponse(['message' => 'tache inexistante'], Response::HTTP_NOT_FOUND);
        }

        // Retirer la tâche de la checklist
        $checklist->removeTask($task);

        // Mettre à jour la base de données
        $this->entityManager->flush();

		
        return new JsonResponse(['message' => 'Tache retirée'], Response::HTTP_OK); 
    }
}
