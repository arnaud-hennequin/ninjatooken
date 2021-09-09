<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class NewsletterCommand extends Command
{
    private $translator;
    private $mailer;
    private $from;
    private $logger;
    private $router;
    private $em;

    public function __construct(TranslatorInterface $translator, MailerInterface $mailer, ParameterBagInterface $params, LoggerInterface $logger, UrlGeneratorInterface $router, EntityManagerInterface $em)
    {
        $this->translator = $translator;
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
		$restrict = " old_id=641 AND";

		if ($input->getOption('all')) {
			$restrict = "";
        }

        $request = 'SELECT id, username, email, auto_login, locale FROM nt_user WHERE'.$restrict.' enabled=1 AND locked=0 ORDER BY id ASC LIMIT ';
        $start = 0;
        $num = 100;
        $i = 1;

        $stmt = $this->em->getConnection()->prepare($request.$start.','.$num);
        $stmt->execute();
        $users = $stmt->fetchAll();

        while (count($users)>0) {

            foreach ($users as $user) {

                try {

                    $username = $user['username'];
                    $email = $user['email'];
                    $locale = $user['locale'];

                    if (empty($locale)) {
                        $locale = 'fr';
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
                        ->text($this->translator->trans('newsletter.text', ['%username%' => $username, '%autologin%' => $auto_login], 'common'))
                        ->context([
                            'mail' => $email,
                            'username' => $username,
                            'message' => $this->translator->trans('newsletter.body', ['%username%' => $username, '%autologin%' => $auto_login], 'common'),
                            '_locale' => $locale
                        ])
                    ;

                    $this->mailer->send($templateEmail);

                    $io->writeln($i." ".$username.' ('.$email.')');

                    $this->logger->info($username.' ('.$email.')');

                } catch (\Exception $e) {

                    $io->writeln("<error>".$i." ".$username.' ('.$email.')</error>');
                    $this->logger->error($username.' ('.$email.') '.$e->getMessage());

                }

                $i++;
            }

            $start += $num;
            
            $stmt = $this->em->getConnection()->prepare($request.$start.','.$num);
            $stmt->execute();
            $users = $stmt->fetchAll();
        }

        $io->writeln('---end');

        return Command::SUCCESS;
    }
}