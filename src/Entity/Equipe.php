<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'equipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Joueur $joueur = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PokemonEquipe>
     */
    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: PokemonEquipe::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $pokemonEquipes;

    public function __construct()
    {
        $this->pokemonEquipes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getJoueur(): ?Joueur
    {
        return $this->joueur;
    }

    public function setJoueur(?Joueur $joueur): self
    {
        $this->joueur = $joueur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PokemonEquipe>
     */
    public function getPokemonEquipes(): Collection
    {
        return $this->pokemonEquipes;
    }

    public function addPokemonEquipe(PokemonEquipe $pokemonEquipe): self
    {
        if (!$this->pokemonEquipes->contains($pokemonEquipe)) {
            $this->pokemonEquipes->add($pokemonEquipe);
            $pokemonEquipe->setEquipe($this);
        }

        return $this;
    }

    public function removePokemonEquipe(PokemonEquipe $pokemonEquipe): self
    {
        if ($this->pokemonEquipes->removeElement($pokemonEquipe)) {
            if ($pokemonEquipe->getEquipe() === $this) {
                $pokemonEquipe->setEquipe(null);
            }
        }

        return $this;
    }
}
