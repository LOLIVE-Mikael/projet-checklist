<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Repository\ChecklistRepository;
use App\Controller\Api\ApiChecklistController;
use App\Controller\Api\ApiChecklistTaskController;
use App\Entity\Visite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use DateInterval;
use DateTime;


#[ORM\Entity(repositoryClass: ChecklistRepository::class)]
#[ApiResource(
	operations: [
		new Post(),
	    new Delete(
			name: 'remove_task', 
			uriTemplate: 'checklists/{checklistid}/tasks/{tacheid}', 
			read: false,
			controller: ApiChecklistTaskController::class,
			openapi: new Operation(
				summary: 'retirer une tache d\'une checklist',
				description: 'retire la tache avec l\'id "tacheid" de la checklist avec l\'id "checklistid"',
				parameters: [
					new Parameter('checklistid', 'path',
						'Identifiant de la checklist',
						true,false,false,
						['type' => 'integer']),
					new Parameter('tacheid','path',
						'Identifiant de la tache',
						true,false,false,
						['type' => 'integer']),
				]
			)
		),
	    new Delete(
            name: 'delete_checklist', 
            controller: ApiChecklistController::class,
        )
	]
)]

class Checklist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'integer'
			]
    )]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit comporter plus de {{ limit }} caracteres.",
        maxMessage: "Le titre ne peux pas faire plus de {{ limit }} caracteres."
    )]
    #[Groups(['read'])]
    private string $title;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\ManyToMany(targetEntity: Task::class, inversedBy: 'checklist', cascade: ["persist"])]
    private Collection $tasks;

    /**
     * @var Collection<int, Visite>
     */
    #[ORM\OneToMany(targetEntity: Visite::class, mappedBy: 'checklist')]
    private $visites;


    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->visites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks->filter(function (Task $tasks) {
			return !$tasks->isArchived();
		});
        return $this->tasks;
    }

    /**
     * Get visites
     *
     * @return Collection|Visite[]
     */
    public function getVisites(): Collection
    {
        return $this->visites;
    }


    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        $this->tasks->removeElement($task);

        return $this;
    }

    public function getDuration(): \DateInterval
    {
		// pour ajouter des DateInterval, il faut :
		//			- créer deux dates (DateTime) identiques
		//			- ajouter les différents DateInterval à l'une des dates
		//			- regarder l'écart entre les deux dates.
		
		$ref = new DateTime('00:00');
		$total = clone $ref;
		
        foreach ($this->getTasks() as $task) {
			if (!$task->isArchived()){
				$total->add($task->getDuration());
			}
        }
		$totalDuration = $total->diff($ref);
		
        return $totalDuration;
    }

    /**
     * Delete the checklist and associated past visits
     *
     * @param EntityManagerInterface $entityManager
     * @throws \Exception
     */
    public function deleteChecklist(EntityManagerInterface $entityManager): void
    {
        $now = new \DateTime();

        foreach ($this->getVisites() as $visite) {
            if ($visite->getDate() < $now) {
                // Supprimer la visite si elle est passée
                $entityManager->remove($visite);
            } else {
                // Lever une erreur si la visite n'est pas passée
                throw new BadRequestHttpException('Cannot delete checklist because it has future visits.');
            }
        }

        // Supprimer la checklist
        $entityManager->remove($this);
        $entityManager->flush();
    }


}
