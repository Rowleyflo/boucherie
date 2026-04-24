<?php

namespace App\Controller\Admin;

use App\Entity\EmailTemplate;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/email-templates')]
#[IsGranted('ROLE_AGENCE')]
class EmailTemplateController extends AbstractController
{
    #[Route('', name: 'admin_email_templates', methods: ['GET'])]
    public function index(EmailTemplateRepository $repo): Response
    {
        return $this->render('admin/email_templates/index.html.twig', [
            'templates'  => $repo->findAll(),
            'variables'  => EmailTemplate::VARIABLES,
        ]);
    }

    #[Route('/{id}', name: 'admin_email_templates_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(EmailTemplate $template, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['sujet'])) { $template->setSujet(trim($data['sujet'])); }
        if (isset($data['corps'])) { $template->setCorps($data['corps']); }

        $em->flush();

        return $this->json([
            'success'    => true,
            'updated_at' => $template->getUpdatedAt()->format('d/m/Y H:i'),
        ]);
    }
}
