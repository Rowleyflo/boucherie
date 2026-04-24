<?php

namespace App\Entity;

use App\Repository\TarifConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifConfigRepository::class)]
#[ORM\Table(name: 'tarifs_config')]
#[ORM\HasLifecycleCallbacks]
class TarifConfig implements \JsonSerializable
{
    public const UNITES = [
        'par_personne' => 'Par personne',
        'forfait'      => 'Forfait',
        'sur_devis'    => 'Sur devis',
        'au_kg'        => 'Au kg',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PrestationConfig::class, inversedBy: 'tarifs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PrestationConfig $prestation;

    #[ORM\Column(length: 150)]
    private string $label;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, options: ['default' => '0.00'])]
    private string $prixBase = '0.00';

    #[ORM\Column(length: 20, options: ['default' => 'par_personne'])]
    private string $unite = 'par_personne';

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $minPersonnes = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $maxPersonnes = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $actif = true;

    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    private int $ordre = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    public function __construct()
    {
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
            'id'            => $this->id,
            'label'         => $this->label,
            'description'   => $this->description,
            'prix_base'     => $this->prixBase,
            'prix_formatted'=> $this->getPrixFormatted(),
            'unite'         => $this->unite,
            'min_personnes' => $this->minPersonnes,
            'max_personnes' => $this->maxPersonnes,
            'actif'         => $this->actif,
            'ordre'         => $this->ordre,
        ];
    }

    public function getUniteLabel(): string
    {
        return self::UNITES[$this->unite] ?? $this->unite;
    }

    public function getPrixFormatted(): string
    {
        return number_format((float) $this->prixBase, 2, ',', ' ') . ' €';
    }

    public function getId(): ?int { return $this->id; }

    public function getPrestation(): PrestationConfig { return $this->prestation; }
    public function setPrestation(PrestationConfig $prestation): static { $this->prestation = $prestation; return $this; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getPrixBase(): string { return $this->prixBase; }
    public function setPrixBase(string $prixBase): static { $this->prixBase = $prixBase; return $this; }

    public function getUnite(): string { return $this->unite; }
    public function setUnite(string $unite): static { $this->unite = $unite; return $this; }

    public function getMinPersonnes(): ?int { return $this->minPersonnes; }
    public function setMinPersonnes(?int $min): static { $this->minPersonnes = $min; return $this; }

    public function getMaxPersonnes(): ?int { return $this->maxPersonnes; }
    public function setMaxPersonnes(?int $max): static { $this->maxPersonnes = $max; return $this; }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }

    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
