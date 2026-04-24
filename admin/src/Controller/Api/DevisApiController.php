<?php

namespace App\Controller\Api;

use App\Entity\Devis;
use App\Repository\DevisRepository;
use App\Repository\PrestationConfigRepository;
use App\Service\DevisMailer;
use App\Service\TarifCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class DevisApiController extends AbstractController
{
    #[Route('/config', name: 'api_config', methods: ['GET'])]
    public function config(PrestationConfigRepository $prestationRepo): JsonResponse
    {
        $prestations = $prestationRepo->findActives();

        $data = [];
        foreach ($prestations as $prestation) {
            $tarifs = [];
            foreach ($prestation->getTarifsActifs() as $tarif) {
                $tarifs[] = [
                    'id'          => $tarif->getId(),
                    'label'       => $tarif->getLabel(),
                    'description' => $tarif->getDescription(),
                    'prix_base'   => (float) $tarif->getPrixBase(),
                    'unite'       => $tarif->getUnite(),
                    'unite_label' => $tarif->getUniteLabel(),
                    'min_personnes' => $tarif->getMinPersonnes(),
                    'max_personnes' => $tarif->getMaxPersonnes(),
                ];
            }

            $data[] = [
                'id'          => $prestation->getId(),
                'slug'        => $prestation->getSlug(),
                'nom'         => $prestation->getNom(),
                'description' => $prestation->getDescription(),
                'icone'       => $prestation->getIcone(),
                'tarifs'      => $tarifs,
            ];
        }

        return $this->corsJson(['prestations' => $data]);
    }

    #[Route('/devis', name: 'api_devis_create', methods: ['POST'])]
    public function create(
        Request                    $request,
        PrestationConfigRepository $prestationRepo,
        DevisRepository            $devisRepo,
        EntityManagerInterface     $em,
        DevisMailer                $mailer,
        TarifCalculator            $calculator,
        LoggerInterface            $logger,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->corsJson(['errors' => ['global' => 'Payload JSON invalide.']], 400);
        }

        $errors = $this->validatePayload($payload, $prestationRepo);
        if ($errors) {
            return $this->corsJson(['errors' => $errors], 422);
        }

        $prestation = $prestationRepo->findOneBy(['slug' => $payload['prestation_slug'], 'actif' => true]);

        $devis = new Devis();
        $devis->setPrestationSlug($prestation->getSlug())
            ->setPrestationNom($prestation->getNom())
            ->setNbPersonnes((int) $payload['nb_personnes'])
            ->setLieu($payload['lieu'] ?? 'a_definir')
            ->setLieuDetail($payload['lieu_detail'] ?? null)
            ->setOptions($payload['options'] ?? null)
            ->setClientNom(trim($payload['client_nom']))
            ->setClientPrenom(trim($payload['client_prenom']))
            ->setClientEmail(strtolower(trim($payload['client_email'])))
            ->setClientTelephone(trim($payload['client_telephone']))
            ->setClientMessage($payload['client_message'] ?? null);

        if (!empty($payload['date_evenement'])) {
            try {
                $devis->setDateEvenement(new \DateTime($payload['date_evenement']));
            } catch (\Exception) {}
        }

        if (!empty($payload['options']) && is_array($payload['options'])) {
            $estimation = $calculator->calculate($prestation, $payload['options'], (int) $payload['nb_personnes']);
            $devis->setEstimationMin((string) $estimation['min'])
                  ->setEstimationMax((string) $estimation['max']);
        } elseif (isset($payload['estimation_min'], $payload['estimation_max'])) {
            $devis->setEstimationMin((string) $payload['estimation_min'])
                  ->setEstimationMax((string) $payload['estimation_max']);
        }

        $devis->generateReference($devisRepo->getNextSequenceNumber());
        $em->persist($devis);
        $em->flush();

        try {
            $mailer->sendConfirmationClient($devis);
            $mailer->sendNotificationBoucher($devis);
        } catch (\Throwable $e) {
            $logger->error('Mailer error: ' . $e->getMessage());
        }

        return $this->corsJson([
            'success'   => true,
            'reference' => $devis->getReference(),
            'message'   => 'Votre demande a bien été envoyée. Nous vous recontactons sous 24-48h.',
        ], 201);
    }

    #[Route('/devis', name: 'api_devis_preflight', methods: ['OPTIONS'])]
    public function preflight(): Response
    {
        $response = new Response('', 204);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        $response->headers->set('Access-Control-Max-Age', '3600');
        return $response;
    }

    private function validatePayload(array $payload, PrestationConfigRepository $repo): array
    {
        $errors = [];

        $required = ['prestation_slug', 'nb_personnes', 'client_nom', 'client_prenom', 'client_email', 'client_telephone'];
        foreach ($required as $field) {
            if (empty($payload[$field]) && $payload[$field] !== 0) {
                $errors[$field] = 'Ce champ est obligatoire.';
            }
        }

        if (!isset($errors['client_email']) && !filter_var($payload['client_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['client_email'] = 'Email invalide.';
        }

        if (!isset($errors['nb_personnes'])) {
            $nb = (int) ($payload['nb_personnes'] ?? 0);
            if ($nb < 1 || $nb > 5000) {
                $errors['nb_personnes'] = 'Le nombre de personnes doit être entre 1 et 5000.';
            }
        }

        if (!isset($errors['prestation_slug'])) {
            $prestation = $repo->findOneBy(['slug' => $payload['prestation_slug'], 'actif' => true]);
            if (!$prestation) {
                $errors['prestation_slug'] = 'Prestation inconnue ou inactive.';
            }
        }

        return $errors;
    }

    private function corsJson(array $data, int $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');
        return $response;
    }
}
