<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisRepository::class)]
#[ORM\Table(name: 'devis')]
#[ORM\HasLifecycleCallbacks]
class Devis
{
    public const STATUTS = [
        'nouveau'    => 'Nouveau',
        'lu'         => 'Lu',
        'en_attente' => 'En attente',
        'accepte'    => 'Accepté',
        'refuse'     => 'Refusé',
    ];

    public const STATUT_COLORS = [
        'nouveau'    => 'rouge',
        'lu'         => 'gray',
        'en_attente' => 'yellow',
        'accepte'    => 'green',
        'refuse'     => 'slate',
    ];

    public const LIEUX = [
        'sur_place'  => 'Sur place',
        'livraison'  => 'Livraison',
        'a_definir'  => 'À définir',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private string $reference;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    // Prestation
    #[ORM\Column(length: 50)]
    private string $prestationSlug;

    #[ORM\Column(length: 150)]
    private string $prestationNom;

    #[ORM\Column(type: 'smallint')]
    private int $nbPersonnes;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateEvenement = null;

    #[ORM\Column(length: 20, options: ['default' => 'a_definir'])]
    private string $lieu = 'a_definir';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuDetail = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $options = null;

    // Estimation
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $estimationMin = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $estimationMax = null;

    // Client
    #[ORM\Column(length: 100)]
    private string $clientNom;

    #[ORM\Column(length: 100)]
    private string $clientPrenom;

    #[ORM\Column(length: 180)]
    private string $clientEmail;

    #[ORM\Column(length: 20)]
    private string $clientTelephone;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $clientMessage = null;

    // Admin
    #[ORM\Column(length: 20, options: ['default' => 'nouveau'])]
    private string $statut = 'nouveau';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesAdmin = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'traite_par', nullable: true, onDelete: 'SET NULL')]
    private ?User $traitePar = null;

    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $tokenAcces = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->tokenAcces = bin2hex(random_bytes(32));
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function generateReference(int $sequenceNumber): void
    {
        $this->reference = sprintf('DEV-%s-%04d', date('Y'), $sequenceNumber);
    }

    public function getStatutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutColor(): string
    {
        return self::STATUT_COLORS[$this->statut] ?? 'gray';
    }

    public function getLieuLabel(): string
    {
        return self::LIEUX[$this->lieu] ?? $this->lieu;
    }

    public function isNouveau(): bool
    {
        return $this->statut === 'nouveau';
    }

    public function getEstimationFormatted(): string
    {
        if ($this->estimationMin === null || $this->estimationMax === null) {
            return 'Non calculée';
        }
        return number_format((float) $this->estimationMin, 0, ',', ' ')
            . ' – '
            . number_format((float) $this->estimationMax, 0, ',', ' ')
            . ' €';
    }

    // Getters & setters
    public function getId(): ?int { return $this->id; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = $reference; return $this; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function getPrestationSlug(): string { return $this->prestationSlug; }
    public function setPrestationSlug(string $slug): static { $this->prestationSlug = $slug; return $this; }

    public function getPrestationNom(): string { return $this->prestationNom; }
    public function setPrestationNom(string $nom): static { $this->prestationNom = $nom; return $this; }

    public function getNbPersonnes(): int { return $this->nbPersonnes; }
    public function setNbPersonnes(int $nb): static { $this->nbPersonnes = $nb; return $this; }

    public function getDateEvenement(): ?\DateTime { return $this->dateEvenement; }
    public function setDateEvenement(?\DateTime $date): static { $this->dateEvenement = $date; return $this; }

    public function getLieu(): string { return $this->lieu; }
    public function setLieu(string $lieu): static { $this->lieu = $lieu; return $this; }

    public function getLieuDetail(): ?string { return $this->lieuDetail; }
    public function setLieuDetail(?string $detail): static { $this->lieuDetail = $detail; return $this; }

    public function getOptions(): ?array { return $this->options; }
    public function setOptions(?array $options): static { $this->options = $options; return $this; }

    public function getEstimationMin(): ?string { return $this->estimationMin; }
    public function setEstimationMin(?string $min): static { $this->estimationMin = $min; return $this; }

    public function getEstimationMax(): ?string { return $this->estimationMax; }
    public function setEstimationMax(?string $max): static { $this->estimationMax = $max; return $this; }

    public function getClientNom(): string { return $this->clientNom; }
    public function setClientNom(string $nom): static { $this->clientNom = $nom; return $this; }

    public function getClientPrenom(): string { return $this->clientPrenom; }
    public function setClientPrenom(string $prenom): static { $this->clientPrenom = $prenom; return $this; }

    public function getClientEmail(): string { return $this->clientEmail; }
    public function setClientEmail(string $email): static { $this->clientEmail = $email; return $this; }

    public function getClientTelephone(): string { return $this->clientTelephone; }
    public function setClientTelephone(string $tel): static { $this->clientTelephone = $tel; return $this; }

    public function getClientMessage(): ?string { return $this->clientMessage; }
    public function setClientMessage(?string $msg): static { $this->clientMessage = $msg; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getNotesAdmin(): ?string { return $this->notesAdmin; }
    public function setNotesAdmin(?string $notes): static { $this->notesAdmin = $notes; return $this; }

    public function getTraitePar(): ?User { return $this->traitePar; }
    public function setTraitePar(?User $user): static { $this->traitePar = $user; return $this; }

    public function getTokenAcces(): ?string { return $this->tokenAcces; }
    public function setTokenAcces(?string $token): static { $this->tokenAcces = $token; return $this; }
}
