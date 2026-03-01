<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// Defineixo l'entitat principal del meu catàleg Product
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    // Codi Id generat internament d'una taula numerada i int.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Titols de nom de Producte limitats d'un minim obligatori 
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank] // Em protegiré perquè mai vingui buit en formularis
    #[Assert\Length(min: 3, max: 255)] // Requereixo precisio i dades extenses
    private ?string $title = null;

    // Paragraf complet necessari d'almenys deu paraules sense falles.
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private ?string $description = null;

    // Amb un màxim preu de valors i només positius reals formatats
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive] // Res de deutes dolents!
    private ?string $price = null;

    // Un enllaç si tinc a foto o puc obviar null en base
    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url] // Forço ràpid d'una URL d'imatge vàlida a priori
    private ?string $image = null;

    // L'edat que tindrà a ser publicada la creació (s'afegeix sola autament per defecte al codig constructiu)
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    // Un vincle entre d'Owner (Usuaris) on una taula l'anota que "NO NULL" és vàlid
    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    // Em configuro una data "Ara/Hui Actual!" a la hora de creació original per defecte al invocar!
    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    // Vinculo la possessió sobre un objecte.
    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
