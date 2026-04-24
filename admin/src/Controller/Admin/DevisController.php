<?php

namespace App\Controller\Admin;

use App\Entity\Devis;
use App\Repository\DevisRepository;
use App\Repository\PrestationConfigRepository;
use App\Service\DevisMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/devis')]
#[IsGranted('ROLE_USER')]
class DevisController extends AbstractController
{
    #[Route('', name: 'admin_devis_index', methods: ['GET'])]
    public function index(Request $request, DevisRepository $devisRepo, PrestationConfigRepository $prestationRepo): Response
    {
        $filters = [
            'statut'      => $request->query->get('statut'),
            'prestation'  => $request->query->get('prestation'),
            'search'      => $request->query->get('search'),
            'date_from'   => $request->query->get('date_from'),
            'date_to'     => $request->query->get('date_to'),
        ];
        $filters = array_filter($filters);

        $page       = max(1, (int) $request->query->get('page', 1));
        $pagination = $devisRepo->findFiltered($filters, $page, 20);
        $prestations = $prestationRepo->findAllOrdered();

        return $this->render('admin/devis/index.html.twig', [
            'pagination'  => $pagination,
            'filters'     => $filters,
            'prestations' => $prestations,
            'statuts'     => Devis::STATUTS,
        ]);
    }

    #[Route('/acces/{token}', name: 'admin_devis_acces_direct', methods: ['GET'])]
    public function accesDirectToken(string $token, DevisRepository $devisRepo): Response
    {
        $devis = $devisRepo->findByToken($token);
        if (!$devis) {
            throw $this->createNotFoundException('Lien invalide ou expiré.');
        }

        if (!$this->getUser()) {
            // Mémoriser le token en session pour rediriger après login
            return $this->redirectToRoute('app_login', ['_target_path' => $this->generateUrl('admin_devis_acces_direct', ['token' => $token])]);
        }

        return $this->redirectToRoute('admin_devis_show', ['id' => $devis->getId()]);
    }

    #[Route('/{id}', name: 'admin_devis_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Devis $devis, EntityManagerInterface $em): Response
    {
        if ($devis->isNouveau()) {
            $devis->setStatut('lu');
            $em->flush();
        }

        return $this->render('admin/devis/show.html.twig', [
            'devis'   => $devis,
            'statuts' => Devis::STATUTS,
        ]);
    }

    #[Route('/{id}/statut', name: 'admin_devis_statut', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateStatut(Devis $devis, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data   = json_decode($request->getContent(), true);
        $statut = $data['statut'] ?? null;

        if (!array_key_exists($statut, Devis::STATUTS)) {
            return $this->json(['error' => 'Statut invalide.'], 422);
        }

        $devis->setStatut($statut);
        $devis->setTraitePar($this->getUser());
        $em->flush();

        return $this->json([
            'success'       => true,
            'statut'        => $statut,
            'statut_label'  => $devis->getStatutLabel(),
            'statut_color'  => $devis->getStatutColor(),
        ]);
    }

    #[Route('/{id}/notes', name: 'admin_devis_notes', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function saveNotes(Devis $devis, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $devis->setNotesAdmin($data['notes'] ?? null);
        $devis->setTraitePar($this->getUser());
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/email', name: 'admin_devis_email', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function sendEmail(Devis $devis, Request $request, DevisMailer $mailer): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');
        $sujet   = trim($data['sujet'] ?? '') ?: null;

        if (empty($message)) {
            return $this->json(['error' => 'Le message ne peut pas être vide.'], 422);
        }

        try {
            $mailer->sendReponsePersonnalisee($devis, $message, $sujet);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur lors de l\'envoi : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/modifier', name: 'admin_devis_modifier', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function modifier(Devis $devis, Request $request, EntityManagerInterface $em, DevisMailer $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['prestation_nom']) && trim($data['prestation_nom']) !== '') {
            $devis->setPrestationNom(trim($data['prestation_nom']));
        }
        if (isset($data['nb_personnes']) && (int) $data['nb_personnes'] > 0) {
            $devis->setNbPersonnes((int) $data['nb_personnes']);
        }
        if (array_key_exists('date_evenement', $data)) {
            try {
                $devis->setDateEvenement($data['date_evenement'] ? new \DateTime($data['date_evenement']) : null);
            } catch (\Exception) {}
        }
        if (isset($data['lieu']) && array_key_exists($data['lieu'], Devis::LIEUX)) {
            $devis->setLieu($data['lieu']);
        }
        if (array_key_exists('lieu_detail', $data)) {
            $devis->setLieuDetail(trim($data['lieu_detail']) ?: null);
        }
        if (array_key_exists('estimation_min', $data)) {
            $devis->setEstimationMin($data['estimation_min'] !== '' ? (string)(float) $data['estimation_min'] : null);
        }
        if (array_key_exists('estimation_max', $data)) {
            $devis->setEstimationMax($data['estimation_max'] !== '' ? (string)(float) $data['estimation_max'] : null);
        }

        $devis->setTraitePar($this->getUser());
        $em->flush();

        $envoyer = !empty($data['envoyer_client']);
        if ($envoyer) {
            try {
                $mailer->sendConfirmationClient($devis);
            } catch (\Throwable $e) {
                return $this->json(['success' => true, 'email_error' => $e->getMessage()]);
            }
        }

        return $this->json([
            'success'       => true,
            'email_envoye'  => $envoyer,
            'estimation'    => $devis->getEstimationFormatted(),
        ]);
    }
}
