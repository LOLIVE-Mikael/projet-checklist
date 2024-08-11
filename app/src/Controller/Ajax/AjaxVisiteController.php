<?php

namespace App\Controller\Ajax;

use App\Entity\Visite;
use App\Entity\Checklist;
use App\Entity\Site;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\VisiteFormService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ApiErrorHandler;

#[Route('/ajax/visite')]
class AjaxVisiteController extends AbstractController
{

    private $visiteFormService;
	private $errorHandler;

    public function __construct(VisiteFormService $visiteFormService, ApiErrorHandler $errorHandler)
    {
        $this->visiteFormService = $visiteFormService;
        $this->errorHandler = $errorHandler;
    }

    #[Route('/add', name: 'app_ajax_visite')]
    public function addTask(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Décoder les données JSON de la requête
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier et récupérer les données nécessaires
        $date = $data['date'] ?? null;
        $siteId = $data['site'] ?? null;
        $userId = $data['user'] ?? null;
        $checklistId = $data['checklist'] ?? null;

        if (!$date || !$siteId || !$userId || !$checklistId) {
			return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_BAD_REQUEST,
				'Donnée manquante'
			);
        }

        $site = $entityManager->getRepository(Site::class)->find($siteId);
        $user = $entityManager->getRepository(User::class)->find($userId);
        $checklist = $entityManager->getRepository(Checklist::class)->find($checklistId);
    
        if (!$site || !$user || !$checklist) {
            return $this->errorHandler->createCustomErrorResponse(
				JsonResponse::HTTP_BAD_REQUEST,
				'Données non valides'
			);
        }
    
        // Créer une nouvelle visite
        $visite = new Visite();
        $visite->setDateFromString($date);
        $visite->setSite($site);
        $visite->setChecklist($checklist);
        $visite->setUser($user);

        try {
            $entityManager->persist($visite);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->errorHandler->createErrorResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Créer le formulaire
        $form = $this->visiteFormService->createEditVisiteForm($visite)->createView();

        // Rendre le formulaire dans le template
        return $this->render('visite/formvisite.html.twig', [
            'form' => $form,
            'visiteId' => $visite->getId(),
        ]);
    }
}
