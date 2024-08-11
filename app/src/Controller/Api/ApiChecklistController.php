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

class ApiChecklistController extends AbstractController
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
	
    #[Route('/api/checklists/{id}', name: 'api_checklist_delete', methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $checklist = $this->checklistRepository->find($id);

        if (!$checklist) {
            throw new NotFoundHttpException('Checklist inexistante');
        }

        try {
            // Appeler la méthode deleteChecklist directement sur l'entité
            $checklist->deleteChecklist($this->entityManager);
            return new JsonResponse(['status' => 'Checklist effacée'], Response::HTTP_OK);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
