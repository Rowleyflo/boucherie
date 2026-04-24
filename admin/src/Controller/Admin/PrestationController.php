<?php

namespace App\Controller\Admin;

use App\Entity\PrestationConfig;
use App\Entity\TarifConfig;
use App\Repository\PrestationConfigRepository;
use App\Repository\TarifConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/prestations')]
#[IsGranted('ROLE_USER')]
class PrestationController extends AbstractController
{
    #[Route('', name: 'admin_prestations_index', methods: ['GET'])]
    public function index(PrestationConfigRepository $repo): Response
    {
        return $this->render('admin/prestations/index.html.twig', [
            'prestations' => $repo->findAllOrdered(),
            'unites'      => TarifConfig::UNITES,
        ]);
    }

    #[Route('/{id}', name: 'admin_prestations_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(PrestationConfig $prestation, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom']))         { $prestation->setNom(trim($data['nom'])); }
        if (isset($data['description'])) { $prestation->setDescription(trim($data['description'])); }
        if (isset($data['icone']))       { $prestation->setIcone(trim($data['icone'])); }
        if (isset($data['actif']))       { $prestation->setActif((bool) $data['actif']); }

        $em->flush();

        return $this->json([
            'success'    => true,
            'updated_at' => $prestation->getUpdatedAt()->format('d/m/Y H:i'),
        ]);
    }

    #[Route('', name: 'admin_prestations_create', methods: ['PUT'])]
    #[IsGranted('ROLE_AGENCE')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom']) || empty($data['slug'])) {
            return $this->json(['error' => 'Nom et slug sont obligatoires.'], 422);
        }

        $prestation = new PrestationConfig();
        $prestation->setSlug(preg_replace('/[^a-z0-9_-]/', '', strtolower($data['slug'])))
            ->setNom(trim($data['nom']))
            ->setDescription(trim($data['description'] ?? ''))
            ->setIcone($data['icone'] ?? '🍖')
            ->setActif((bool) ($data['actif'] ?? true));

        $em->persist($prestation);
        $em->flush();

        return $this->json([
            'success' => true,
            'id'      => $prestation->getId(),
            'slug'    => $prestation->getSlug(),
            'nom'     => $prestation->getNom(),
        ], 201);
    }

    #[Route('/{id}', name: 'admin_prestations_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_AGENCE')]
    public function delete(PrestationConfig $prestation, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($prestation);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/reorder', name: 'admin_prestations_reorder', methods: ['POST'])]
    #[IsGranted('ROLE_AGENCE')]
    public function reorder(Request $request, PrestationConfigRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids  = $data['ids'] ?? [];

        foreach ($ids as $ordre => $id) {
            $prestation = $repo->find((int) $id);
            if ($prestation) {
                $prestation->setOrdre((int) $ordre);
            }
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/tarifs/{id}', name: 'admin_prestations_tarif_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateTarif(TarifConfig $tarif, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['label']))         { $tarif->setLabel(trim($data['label'])); }
        if (isset($data['description']))   { $tarif->setDescription(trim($data['description']) ?: null); }
        if (isset($data['prix_base']))     { $tarif->setPrixBase(number_format((float) $data['prix_base'], 2, '.', '')); }
        if (isset($data['unite']))         { $tarif->setUnite($data['unite']); }
        if (array_key_exists('min_personnes', $data)) { $tarif->setMinPersonnes($data['min_personnes'] ? (int) $data['min_personnes'] : null); }
        if (array_key_exists('max_personnes', $data)) { $tarif->setMaxPersonnes($data['max_personnes'] ? (int) $data['max_personnes'] : null); }
        if (isset($data['actif']))         { $tarif->setActif((bool) $data['actif']); }

        $em->flush();

        return $this->json([
            'success'        => true,
            'prix_formatted' => $tarif->getPrixFormatted(),
            'updated_at'     => $tarif->getUpdatedAt()->format('d/m/Y H:i'),
        ]);
    }

    #[Route('/{id}/tarifs', name: 'admin_prestations_tarif_create', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function createTarif(PrestationConfig $prestation, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['label'])) {
            return $this->json(['error' => 'Le libellé est obligatoire.'], 422);
        }

        $tarif = new TarifConfig();
        $tarif->setLabel(trim($data['label']))
            ->setDescription(trim($data['description'] ?? '') ?: null)
            ->setPrixBase(number_format((float) ($data['prix_base'] ?? 0), 2, '.', ''))
            ->setUnite($data['unite'] ?? 'par_personne')
            ->setMinPersonnes($data['min_personnes'] ? (int) $data['min_personnes'] : null)
            ->setMaxPersonnes($data['max_personnes'] ? (int) $data['max_personnes'] : null)
            ->setActif((bool) ($data['actif'] ?? true));

        $prestation->addTarif($tarif);
        $em->persist($tarif);
        $em->flush();

        return $this->json([
            'success'        => true,
            'id'             => $tarif->getId(),
            'label'          => $tarif->getLabel(),
            'prix_formatted' => $tarif->getPrixFormatted(),
            'unite_label'    => $tarif->getUniteLabel(),
        ], 201);
    }

    #[Route('/tarifs/{id}', name: 'admin_prestations_tarif_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteTarif(TarifConfig $tarif, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tarif);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
