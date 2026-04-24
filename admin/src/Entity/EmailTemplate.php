<?php

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailTemplateRepository::class)]
#[ORM\Table(name: 'email_templates')]
#[ORM\HasLifecycleCallbacks]
class EmailTemplate
{
    public const VARIABLES = [
        '{{reference}}'            => 'Référence du devis (ex: DEV-2025-0042)',
        '{{client_prenom}}'        => 'Prénom du client',
        '{{client_nom}}'           => 'Nom du client',
        '{{client_email}}'         => 'Adresse email du client',
        '{{client_telephone}}'     => 'Téléphone du client',
        '{{prestation_nom}}'       => 'Nom de la prestation',
        '{{nb_personnes}}'         => 'Nombre de personnes',
        '{{date_evenement}}'       => 'Date de l\'événement (format j/m/Y)',
        '{{lieu}}'                 => 'Lieu de l\'événement',
        '{{estimation_min}}'       => 'Estimation minimale (ex: 2 100,00 €)',
        '{{estimation_max}}'       => 'Estimation maximale (ex: 2 600,00 €)',
        '{{lien_admin}}'           => 'Lien direct vers la fiche dans le back-office',
        '{{message_personnalise}}' => 'Message saisi manuellement par le boucher',
        '{{nom_boucher}}'          => 'Nom de la boucherie',
        '{{annee}}'                => 'Année en cours',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $slug;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(length: 255)]
    private string $sujet;

    #[ORM\Column(type: 'text')]
    private string $corps;

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

    /**
     * Remplace toutes les variables du template par les valeurs du devis.
     *
     * @return array{sujet: string, corps: string}
     */
    public function render(Devis $devis, array $extra = []): array
    {
        $vars = array_merge([
            '{{reference}}'        => $devis->getReference(),
            '{{client_prenom}}'    => $devis->getClientPrenom(),
            '{{client_nom}}'       => $devis->getClientNom(),
            '{{client_email}}'     => $devis->getClientEmail(),
            '{{client_telephone}}' => $devis->getClientTelephone(),
            '{{prestation_nom}}'   => $devis->getPrestationNom(),
            '{{nb_personnes}}'     => (string) $devis->getNbPersonnes(),
            '{{date_evenement}}'   => $devis->getDateEvenement()?->format('d/m/Y') ?? 'À définir',
            '{{lieu}}'             => $devis->getLieuLabel(),
            '{{estimation_min}}'   => $devis->getEstimationMin()
                ? number_format((float) $devis->getEstimationMin(), 2, ',', ' ') . ' €'
                : 'À confirmer',
            '{{estimation_max}}'   => $devis->getEstimationMax()
                ? number_format((float) $devis->getEstimationMax(), 2, ',', ' ') . ' €'
                : 'À confirmer',
            '{{annee}}'            => date('Y'),
        ], $extra);

        $sujet = str_replace(array_keys($vars), array_values($vars), $this->sujet);
        $corps = str_replace(array_keys($vars), array_values($vars), $this->corps);

        return ['sujet' => $sujet, 'corps' => $corps];
    }

    public function getId(): ?int { return $this->id; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getSujet(): string { return $this->sujet; }
    public function setSujet(string $sujet): static { $this->sujet = $sujet; return $this; }

    public function getCorps(): string { return $this->corps; }
    public function setCorps(string $corps): static { $this->corps = $corps; return $this; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
