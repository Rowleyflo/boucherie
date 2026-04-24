<?php

namespace App\Entity;

use App\Repository\PrestationConfigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationConfigRepository::class)]
#[ORM\Table(name: 'prestations_config')]
#[ORM\HasLifecycleCallbacks]
class PrestationConfig implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $slug;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(length: 10, options: ['default' => '🍖'])]
    private string $icone = '🍖';

    #[ORM\Column(options: ['default' => true])]
    private bool $actif = true;

    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    private int $ordre = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    #[ORM\OneToMany(
        targetEntity: TarifConfig::class,
        mappedBy: 'prestation',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $tarifs;

    public function __construct()
    {
        $this->tarifs = new ArrayCollection();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'nom'         => $this->nom,
            'description' => $this->description,
            'icone'       => $this->icone,
            'actif'       => $this->actif,
            'ordre'       => $this->ordre,
            'tarifs'      => $this->tarifs->map(fn(TarifConfig $t) => $t->jsonSerialize())->toArray(),
        ];
    }

    public function getTarifsActifs(): Collection
    {
        return $this->tarifs->filter(fn(TarifConfig $t) => $t->isActif());
    }

    public function getId(): ?int { return $this->id; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getIcone(): string { return $this->icone; }
    public function setIcone(string $icone): static { $this->icone = $icone; return $this; }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }

    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function getTarifs(): Collection { return $this->tarifs; }

    public function addTarif(TarifConfig $tarif): static
    {
        if (!$this->tarifs->contains($tarif)) {
            $this->tarifs->add($tarif);
            $tarif->setPrestation($this);
        }
        return $this;
    }

    public function removeTarif(TarifConfig $tarif): static
    {
        $this->tarifs->removeElement($tarif);
        return $this;
    }
}
