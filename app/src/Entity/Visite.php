<?php

namespace App\Entity;

use App\Repository\VisiteRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisiteRepository::class)]
#[ApiResource(
	operations: [
	    new Delete(),
	    new Patch(),
		new Post(),
		new Get(),
	],
    normalizationContext: ['groups' => ['read']],
)]

class Visite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['read'])]
    #[Assert\NotNull(message: "Vous devez affecter un site.")]
    private ?Site $site;

    #[ORM\ManyToOne]
    #[Groups(['read'])]
    #[Assert\NotNull(message: "Vous devez affecter un technicien.")]
    private ?User $user;

    #[ORM\ManyToOne]
    #[Groups(['read'])]
    private ?Checklist $checklist;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable :false)]
    #[Assert\NotNull(message: "La date ne peut pas être vide.")]
    #[Assert\GreaterThanOrEqual("today")]
    #[Groups(['read'])] 
    private ?\DateTimeInterface $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $User): static
    {
        $this->user = $User;

        return $this;
    }

    public function getChecklist(): ?Checklist
    {
        return $this->checklist;
    }

    public function setChecklist(?Checklist $checklist): static
    {
        $this->checklist = $checklist;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function setDateFromString(string $temps): static
	{
		$date = new \DateTimeImmutable($temps);
        $this->date = $date;

        return $this;		
	}
}
