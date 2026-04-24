<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un nouvel utilisateur (boucher ou agence)',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Création d\'un nouvel utilisateur');

        $helper = $this->getHelper('question');

        $q = new Question('Email : ');
        $q->setValidator(function ($value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Email invalide.');
            }
            return $value;
        });
        $email = $helper->ask($input, $output, $q);

        $q = new Question('Prénom : ');
        $prenom = $helper->ask($input, $output, $q);

        $q = new Question('Nom : ');
        $nom = $helper->ask($input, $output, $q);

        $q = new Question('Mot de passe : ');
        $q->setHidden(true);
        $q->setHiddenFallback(false);
        $q->setValidator(function ($value) {
            if (strlen($value) < 8) {
                throw new \RuntimeException('Le mot de passe doit faire au moins 8 caractères.');
            }
            return $value;
        });
        $password = $helper->ask($input, $output, $q);

        $q = new ChoiceQuestion('Rôle :', ['ROLE_BOUCHER', 'ROLE_AGENCE'], 0);
        $role = $helper->ask($input, $output, $q);

        $user = new User();
        $user->setEmail($email)
            ->setPrenom($prenom)
            ->setNom($nom)
            ->setRoles([$role])
            ->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Utilisateur %s (%s) créé avec succès !', $user->getNomComplet(), $email));

        return Command::SUCCESS;
    }
}
