<?php

namespace App\Controller\Admin;

use App\Repository\DevisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(DevisRepository $devisRepo): Response
    {
        $countsByStatut = $devisRepo->countByStatut();
        $countsByDay    = $devisRepo->countByDay(30);
        $derniers       = $devisRepo->findBy([], ['createdAt' => 'DESC'], 5);

        $chartLabels = array_column($countsByDay, 'jour');
        $chartData   = array_column($countsByDay, 'total');

        return $this->render('admin/dashboard.html.twig', [
            'counts_by_statut' => $countsByStatut,
            'derniers_devis'   => $derniers,
            'chart_labels'     => json_encode($chartLabels),
            'chart_data'       => json_encode($chartData),
            'total'            => array_sum($countsByStatut),
        ]);
    }
}
