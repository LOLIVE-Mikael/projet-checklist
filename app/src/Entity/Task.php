<?php
 
namespace App\Entity;

use Doctrine\ORM\Mapping\Metadata;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Api\ApiDeleteTaskController;
use App\Controller\Api\ApiPatchTaskController;

use DateInterval;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ApiResource(
	operations: [
	    new Delete(
            controller: ApiDeleteTaskController::class
        ),
	    new Patch(
            controller: ApiPatchTaskController::class,            
        ),
		new Post(),
		new Get(),
	],
    normalizationContext: ['groups' => ['read']],
)]

class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.", groups:['task_creation'])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit comporter plus de {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères."
    )]
    #[Groups(['read'])]
    private ?string $title;

    #[ORM\Column]
    #[Groups(['read'])]
    private \DateInterval $duration;

    #[ORM\Column(type: 'boolean')]
    private bool $archived = false;

    /**
     * @var Collection<int, Checklist>
     */
    #[ORM\ManyToMany(targetEntity: Checklist::class, mappedBy: 'tasks')]
    private Collection $checklists;

    public function __construct()
    {
        $this->checklists = new ArrayCollection();
        $this->duration = new DateInterval('P0Y0M0DT0H0M0S');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(\DateInterval $duration): static
    {
        $this->duration = $this->normalizeDateInterval($duration);

        return $this;
    }

    /**
     * @throws \InvalidArgumentException Si le format de durée est invalide.
     */
    public function setDurationFromString(string $temps): static
    {

        try {
            $duration = new \DateInterval($temps);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Le format de durée est invalide.");
        }
        $this->setDuration($duration);

        return $this;
    }

    private function normalizeDateInterval(\DateInterval $interval): \DateInterval
    {
        // Convertir le DateInterval en valeurs totales
        $totalMinutes = $interval->h * 60 + $interval->i;
        $totalHours = intdiv($totalMinutes, 60);
        $remainingMinutes = $totalMinutes % 60;

        // Créer un nouveau DateInterval avec les valeurs normalisées
        $normalizedInterval = new \DateInterval('PT' . $totalHours . 'H' . $remainingMinutes . 'M' . $interval->s . 'S');

        return $normalizedInterval;
    }

    public function isArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): static
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return Collection<int, Checklists>
     */
    public function getChecklists(): Collection
    {
        return $this->checklists;
    }

    public function addChecklist(Checklist $checklist): static
    {
        if (!$this->checklists->contains($checklist)) {
            $this->checklists->add($checklist);
            $checklist->addTask($this);
        }

        return $this;
    }

    public function removeChecklist(Checklist $checklist): static
    {
        if ($this->checklists->removeElement($checklist)) {
            $checklist->removeTask($this);
        }

        return $this;
    }

    public function archive(): void
    {
        $this->removeFromChecklists();
        $this->archived = true;
    }

    private function removeFromChecklists(): void
    {
        foreach ($this->checklists as $checklist) {
            $checklist->removeTask($this);
        }
        $this->checklists->clear();
    }

}
