<?php

namespace App\Service;

use App\Entity\Devis;
use App\Repository\EmailTemplateRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DevisMailer
{
    public function __construct(
        private readonly MailerInterface           $mailer,
        private readonly EmailTemplateRepository   $templateRepository,
        private readonly UrlGeneratorInterface     $urlGenerator,
        private readonly string                    $emailBoucher,
        private readonly string                    $emailFrom,
        private readonly string                    $nomBoucher,
    ) {}

    public function sendConfirmationClient(Devis $devis): void
    {
        $template = $this->templateRepository->findBySlug('confirmation_client');
        if (!$template) {
            return;
        }

        ['sujet' => $sujet, 'corps' => $corps] = $template->render($devis, [
            '{{nom_boucher}}' => $this->nomBoucher,
        ]);

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->nomBoucher, $this->emailFrom))
            ->to($devis->getClientEmail())
            ->subject($sujet)
            ->html($corps);

        $this->mailer->send($email);
    }

    public function sendNotificationBoucher(Devis $devis): void
    {
        $template = $this->templateRepository->findBySlug('notification_boucher');
        if (!$template) {
            return;
        }

        $lienAdmin = $this->urlGenerator->generate(
            'admin_devis_acces_direct',
            ['token' => $devis->getTokenAcces()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        ['sujet' => $sujet, 'corps' => $corps] = $template->render($devis, [
            '{{lien_admin}}'  => $lienAdmin,
            '{{nom_boucher}}' => $this->nomBoucher,
        ]);

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->nomBoucher, $this->emailFrom))
            ->to($this->emailBoucher)
            ->subject($sujet)
            ->html($corps);

        $this->mailer->send($email);
    }

    public function sendReponsePersonnalisee(Devis $devis, string $message, ?string $sujet = null): void
    {
        $template = $this->templateRepository->findBySlug('reponse_devis');

        if ($sujet === null && $template) {
            $rendered = $template->render($devis, [
                '{{message_personnalise}}' => $message,
                '{{nom_boucher}}'          => $this->nomBoucher,
            ]);
            $sujetFinal = $rendered['sujet'];
            $corps      = $rendered['corps'];
        } elseif ($template) {
            $rendered = $template->render($devis, [
                '{{message_personnalise}}' => $message,
                '{{nom_boucher}}'          => $this->nomBoucher,
            ]);
            $sujetFinal = $sujet;
            $corps      = $rendered['corps'];
        } else {
            $sujetFinal = $sujet ?? 'Réponse à votre demande de devis ' . $devis->getReference();
            $corps      = nl2br(htmlspecialchars($message));
        }

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->nomBoucher, $this->emailFrom))
            ->to($devis->getClientEmail())
            ->subject($sujetFinal)
            ->html($corps);

        $this->mailer->send($email);
    }
}
