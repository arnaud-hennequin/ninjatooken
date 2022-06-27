<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsletterCommand extends Command
{
    private Translator $translator;
    private MailerInterface $mailer;
    private Address $from;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $router;
    private EntityManagerInterface $em;

    public function __construct(TranslatorInterface $translator, MailerInterface $mailer, ParameterBagInterface $params, LoggerInterface $logger, UrlGeneratorInterface $router, EntityManagerInterface $em)
    {
        if ($translator instanceof Translator) {
            $this->translator = $translator;
        }
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->router = $router;
        $this->em = $em;

        $this->from = new Address($params->get('mail_contact'), $params->get('mail_name'));

        $context = $this->router->getContext();
        $context->setHost($params->get('base_host'));
        $context->setScheme('http');

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('newsletter:send')
            ->setDescription('Envoi de la newsletter')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Envoi à tous les inscrits ?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('---start');

        // boucle sur les différents utilisateurs
        if ($input->getOption('all')) {
            $restrict = '';
        } else {
            $restrict = ' old_id=641 AND';
        }

        $request = 'SELECT id, username, email, auto_login, locale FROM nt_user WHERE'.$restrict.' enabled=1 AND locked=0 ORDER BY id ASC LIMIT ';
        $start = 0;
        $num = 100;
        $i = 1;

        $stmt = $this->em->getConnection()->prepare($request.$start.','.$num);
        $result = $stmt->executeQuery();
        $users = $result->fetchAllAssociative();

        while (count($users) > 0) {
            foreach ($users as $user) {
                try {
                    $username = $user['username'];
                    $email = $user['email'];

                    if (empty($user['locale'])) {
                        $locale = 'fr';
                    } else {
                        $locale = $user['locale'];
                    }

                    if (empty($user['auto_login'])) {
                        $auto_login = $this->router->generate('ninja_tooken_homepage', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
                    } else {
                        $auto_login = $this->router->generate('ninja_tooken_user_autologin', ['autologin' => $user['auto_login'], '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
                    }

                    // construit le contenu
                    $this->translator->setLocale($locale);

                    // envoi les messages
                    $templateEmail = (new TemplatedEmail())
                        ->from($this->from)
                        ->to($email)
                        ->subject($this->translator->trans('newsletter.subject', ['%username%' => $username], 'common'))
                        ->htmlTemplate('newsletter.html.twig')
                        ->textTemplate('newsletter.text.twig')
                        ->context([
                            'mail' => $email,
                            'username' => $username,
                            'autologin' => $auto_login,
                            '_locale' => $locale,
                        ])
                    ;

                    $this->mailer->send($templateEmail);

                    $io->writeln($i.' '.$username.' ('.$email.')');

                    $this->logger->info($username.' ('.$email.')');
                } catch (\Exception $e) {
                    $io->writeln('<error>'.$i.' '.$username.' ('.$email.')</error>');
                    $this->logger->error($username.' ('.$email.') '.$e->getMessage());
                }

                ++$i;
            }

            $start += $num;

            $stmt = $this->em->getConnection()->prepare($request.$start.','.$num);
            $request = $stmt->executeQuery();
            $users = $request->fetchAllAssociative();
        }

        $io->writeln('---end');

        return Command::SUCCESS;
    }
}
